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
use App\Events\CustomColumnsUpdated;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\JsonResponse;
use Carbon\Carbon;

class ContactsController extends Controller
{
    public function index(): Response | JsonResponse
    {
        Log::info('Store method accessed.', [
            'request_data' => Request::all(),
        ]);
        $filter = Request::input('data', '');
        $id = Request::input('id', '');
        $sDate = Request::input('start_date');
        $eDate = Request::input('end_date');


        //set id in session
        session(['id' => $id]);
        if ($id == '') {
            $id = 'today';
        }
        // Initialize the base query for contacts
        $contactsQuery = Auth::user()->account->contacts()->with('organization');
        if ($sDate && $eDate) {
            $startDate = Carbon::createFromFormat('m/d/Y', $sDate)->format('Y-m-d');
            $endDate = Carbon::createFromFormat('m/d/Y', $eDate)->format('Y-m-d');

            $contactsQuery->whereDate('created_at', '>=', $startDate)
                ->whereDate('created_at', '<=', $endDate);
            // return response()->json($contactsQuery);
        }
        // Apply date filters if provided
        // if ($startDate && $endDate) {
        // } else {
        // Apply default filters based on the 'filter' query parameter
        $contactsQuery->when($filter === 'today', function ($query) {
            $query->whereDate('created_at', today());
        })->when($filter === 'yesterday', function ($query) {
            $query->whereDate('created_at', today()->subDay());
        })->when($filter === 'last7days', function ($query) {
            $query->whereDate('created_at', '>=', today()->subDays(7));
        })->when($filter === 'last30days', function ($query) {
            $query->whereDate('created_at', '>=', today()->subDays(30));
        })->when($filter === 'last90days', function ($query) {
            $query->whereDate('created_at', '>=', today()->subDays(90));
        });
        // }
        // Fetch organizations and custom columns
        $organizations = Organization::all();
        $additionalColumns = Auth::user()->account->contactCustomColumns()->get();

        // Paginate the contacts
        $contacts = $contactsQuery->orderByName()
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
            ]);

        // Count the total contacts
        $totalContacts = $contactsQuery->count();
        // dd($totalContacts);
        Log::info('ContactsController@index');
        // Return the Inertia response
        return Inertia::render('Contacts/Index', [
            'filters' => Request::only('search', 'trashed'),
            'organizations' => $organizations,
            'additionalColumns' => $additionalColumns,
            'contacts' => $contacts,
            'totalContacts' => $totalContacts,
            'id' => session('id'),
        ]);
    }

    // public function setFilter()
    // {
    //     return $this->index();
    // }

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

        if (Auth::user()->account->contactCustomColumns()->where('name', $column['name'])->exists()) {
            return Redirect::back()->with('error', 'Column already exists.');
        } else {

            Auth::user()->account->contactCustomColumns()->create($column);

            return Redirect::back()->with('success', 'Column added.');
        }
    }

    public function updateCustomColumns(Contact $contact): RedirectResponse
    {
        $columns = Request::input('columns');

        foreach ($columns as $column) {
            if (isset($column['value']) && $column['value'] !== null) {
                $contact->contactsCustomData()->updateOrCreate(
                    ['column_id' => $column['id']],
                    ['value' => $column['value']]
                );
            }
        }

        event(new CustomColumnsUpdated($contact, $columns));

        return Redirect::back()->with('success', 'Contact updated.');
    }

    public function deleteCustomColumns($columnId): RedirectResponse
    {

        $column = ContactCustomColumns::find($columnId);
        if ($column && Auth::user()->account->contactCustomColumns->contains($column)) {
            $column->delete();
        }
        return Redirect::back()->with('success', 'Column deleted.');
    }

    public function deleteSelected($ids): RedirectResponse
    {
        if (is_string($ids)) {
            $ids = explode(',', $ids);
        }

        Contact::whereIn('id', $ids)->delete();
        return Redirect::back()->with('success', 'Records Deleted');
    }

    public function deleteAll(): RedirectResponse
    {
        Contact::where('account_id', Auth::user()->account_id)->delete();
        return Redirect::back()->with('success', 'All Records Deleted');
    }
}
