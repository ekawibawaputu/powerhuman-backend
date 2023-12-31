<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Models\Company;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CompanyController extends Controller
{
    //
    public function fetch(Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit', 10);

        // powerhuman.com/api/company?id=1
        if($id)
        {
            $company = Company::with(['users'])->whereHas('users', function($query){
                $query->where('user_id', Auth::id());
            })->find($id);

            if($company)
            {
                return ResponseFormatter::success($company, 'Company found');
            }
            return ResponseFormatter::error('Company not found', 404);
        }
        // powerhuman.com/api/company?id=1&limit=10

        // powerhuman.com/api/company
        $companies = Company::with(['users'])->whereHas('users', function($query) {
            $query->where('user_id', Auth::id());
        });

        // powerhuman.com/api/company?name=John
        if($name) {
            $companies->where('name', 'like','%'. $name . '%');
        }

        return ResponseFormatter::success(
            $companies->paginate($limit),
            'Companies found'
        );
    }

    public function create(CreateCompanyRequest $request)
    {
        try {
            // upload logo
            if($request->hasFile('logo')){
                $path = $request->file('logo')->store('public/logos');
            }

            // create company
            $company = Company::create([
                'name' => $request->name,
                'logo' => isset($path) ? $path : ''
            ]);

            if(!$company){
                throw new Exception('Company could not be created');
            }

            // attach company to user
            $user = User::find(Auth::id());
            $user->companies()->attach($company->id);

            // load company at users
            $company->load('users');
    
            return ResponseFormatter::success($company, 'Company created');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }

    public function update(UpdateCompanyRequest $request, $id)
    {
        try {
            // get company
            $company = Company::find($id);

            if(!$company){
                throw new Exception('Company could not be found');
            }

            // upload logo
            if($request->hasFile('logo')){
                $path = $request->file('logo')->store('public/logos');
            }

            // update company
            $company->update([
                'name' => $request->name,
                'logo' => isset($path) ? $path : $company->logo
            ]);

            return ResponseFormatter::success($company, 'Company updated');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
