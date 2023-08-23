<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateRoleRequest;
use App\Http\Requests\UpdateRoleRequest;
use App\Models\Role;
use Exception;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function fetch (Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit');

        $roleQuery = Role::query();

        // get single role
        if($id){
            $role = $roleQuery->find($id);

            if($role){
                return ResponseFormatter::success($role, 'Role found');
            }
            return ResponseFormatter::error('Role not found', 404);
        }

        // get multiple roles
        $roles = $roleQuery->where('company_id', $request->company_id);

        if($name){
            $roles->where('name','like','%',$name,'%');
        }
        
        return ResponseFormatter::success([
            $roles->paginate($limit),
            'Role found'
        ]);
    }

    public function create(CreateRoleRequest $request)
    {
        try {

            // create role
            $role = Role::create([
                'name' => $request->name,
                'company_id' => $request->company_id
            ]);

            if(!$role) {
                throw new Exception('Role creation failed');
            }

            return ResponseFormatter::success($role, 'Role created');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(),500);
        }
    }

    public function update(UpdateRoleRequest $request, $id)
    {
        try {
            // Get role
            $role = Role::find($id);

            // Check if role exists
            if(!$role) {
                throw new Exception('Role creation failed');
            }

            // update role
            $role->update([
                'name' => $request->name,
                'company_id' => $request->company_id
            ]);

            return ResponseFormatter::success($role,'Role updated');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage()); 
        }
    }

    public function destroy($id)
    {
        try {
            // get role
            $role = Role::find($id);

            // Todo: check if role is owned by user

            // 'check' if role exists
            if(!$role) {
                throw new Exception('Role not found');
            }

            // delete role
            $role->delete();

            return ResponseFormatter::success('Role deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
