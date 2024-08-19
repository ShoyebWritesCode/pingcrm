<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use App\Models\Organization;
use App\Models\ContactCustomColumns;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;
use Mockery\Undefined;

class ContactsController extends Controller
{
    public function index(): Response
    {
        return Inertia::render('Contacts/Index', [
            'filters' => Request::all('search', 'trashed'),
            'organizations' => Organization::all(),
            'additionalColumns' => Auth::user()->account->contactCustomColumns()->get(),
            'contacts' => Auth::user()->account->contacts()
                ->with('organization')
                ->orderByName()
                ->filter(Request::only('search', 'trashed'))
                ->paginate(10)
                ->withQueryString()
                ->through(fn($contact) => [
                    'id' => $contact->id,
                    'name' => $contact->name,
                    'phone' => $contact->phone,
                    'city' => $contact->city,
                    'deleted_at' => $contact->deleted_at,
                    'organization' => $contact->organization ? $contact->organization->only('name') : null,
                    'additional_data' => $contact->additional_data,
                ]),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Contacts/Create', [
            'organizations' => Auth::user()->account
                ->organizations()
                ->orderBy('name')
                ->get()
                ->map
                ->only('id', 'name'),
        ]);
    }

    public function store(): RedirectResponse
    {
        Auth::user()->account->contacts()->create(
            Request::validate([
                'first_name' => ['required', 'max:50'],
                'last_name' => ['required', 'max:50'],
                'organization_id' => ['nullable', Rule::exists('organizations', 'id')->where(function ($query) {
                    $query->where('account_id', Auth::user()->account_id);
                })],
                'email' => ['nullable', 'max:50', 'email'],
                'phone' => ['nullable', 'max:50'],
                'address' => ['nullable', 'max:150'],
                'city' => ['nullable', 'max:50'],
                'region' => ['nullable', 'max:50'],
                'country' => ['nullable', 'max:2'],
                'postal_code' => ['nullable', 'max:25'],
            ])
        );

        return Redirect::route('contacts')->with('success', 'Contact created.');
    }

    public function edit(Contact $contact): Response
    {
        return Inertia::render('Contacts/Edit', [
            'contact' => [
                'id' => $contact->id,
                'first_name' => $contact->first_name,
                'last_name' => $contact->last_name,
                'organization_id' => $contact->organization_id,
                'email' => $contact->email,
                'phone' => $contact->phone,
                'address' => $contact->address,
                'city' => $contact->city,
                'region' => $contact->region,
                'country' => $contact->country,
                'postal_code' => $contact->postal_code,
                'deleted_at' => $contact->deleted_at,
            ],
            'organizations' => Auth::user()->account->organizations()
                ->orderBy('name')
                ->get()
                ->map
                ->only('id', 'name'),

            'customColumns' => Auth::user()->account->contactCustomColumns()->get(),
            'customData' => $contact->contactsCustomData()->get()->map->only('column_id', 'value'),
        ]);
    }

    public function update(Contact $contact): RedirectResponse
    {
        $contact->update(
            Request::validate([
                'first_name' => ['required', 'max:50'],
                'last_name' => ['required', 'max:50'],
                'organization_id' => [
                    'nullable',
                    Rule::exists('organizations', 'id')->where(fn($query) => $query->where('account_id', Auth::user()->account_id)),
                ],
                'email' => ['nullable', 'max:50', 'email'],
                'phone' => ['nullable', 'max:50'],
                'address' => ['nullable', 'max:150'],
                'city' => ['nullable', 'max:50'],
                'region' => ['nullable', 'max:50'],
                'country' => ['nullable', 'max:2'],
                'postal_code' => ['nullable', 'max:25'],
            ])
        );

        return Redirect::back()->with('success', 'Contact updated.');
    }

    public function destroy(Contact $contact): RedirectResponse
    {
        $contact->delete();

        return Redirect::back()->with('success', 'Contact deleted.');
    }

    public function restore(Contact $contact): RedirectResponse
    {
        $contact->restore();

        return Redirect::back()->with('success', 'Contact restored.');
    }

    public function importCsv(): RedirectResponse
    {
        $data = Request::input('data');

        $filteredData = array_filter($data, function ($row) {
            if (empty($row['name'])) {
                return false;
            }

            foreach ($row as $key => $value) {
                if ($key !== 'name' && ($value === null || $value === 'N/A')) {
                    $row[$key] = null;
                }
            }

            return true;
        });

        foreach ($filteredData as $row) {
            auth()->user()->account->contacts()->create([
                'first_name' => explode(' ', $row['name'])[0],
                'last_name' => explode(' ', $row['name'])[1],
                'phone' => $row['phone'] ?? null,
                'city' => $row['city'] ?? null,
                'organization_id' => $row['organization_id'] ?? null,

            ]);
        }

        return Redirect::route('contacts')->with('success', 'Contacts imported.');
    }

    public function addColumn()
    {
        $column = Request::validate([
            'name' => ['required', 'max:50'],
            'type' => ['required', 'in:string,number,date'],
        ]);

        Auth::user()->account->contactCustomColumns()->create($column);

        return Redirect::back()->with('success', 'Column added.');
    }

    public function updateCustomColumns(Contact $contact): RedirectResponse
    {
        $columns = Request::input('columns');
        $additionalData = json_decode($contact->additional_data, true) ?: [];

        foreach ($columns as $column) {
            if (isset($column['value']) && $column['value'] !== null) {
                $contact->contactsCustomData()->updateOrCreate(
                    ['column_id' => $column['id']],
                    ['value' => $column['value']]
                );

                $columnName = ContactCustomColumns::where('id', $column['id'])->value('name');
                $additionalData[$columnName] = $column['value'];
            }
        }
        $contact->update([
            'additional_data' => json_encode($additionalData),
        ]);


        return Redirect::back()->with('success', 'Contact updated.');
    }
}
