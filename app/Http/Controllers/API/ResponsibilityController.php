<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateResponsibilityRequest;
use App\Models\Responsibility;
use Exception;
use Illuminate\Http\Request;

class ResponsibilityController extends Controller
{
    public function fetch (Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit');

        $responsibilityQuery = Responsibility::query();

        // get single responsibility
        if($id){
            $responsibility = $responsibilityQuery->find($id);

            if($responsibility){
                return ResponseFormatter::success($responsibility, 'Responsibility found');
            }
            return ResponseFormatter::error('Responsibility not found', 404);
        }

        // get multiple responsibilities
        $responsibilities = $responsibilityQuery->where('role_id', $request->role_id);

        if($name){
            $responsibilities->where('name','like','%',$name,'%');
        }
        
        return ResponseFormatter::success(
            $responsibilities->paginate($limit),
            'Responsibility found'
        );
    }

    public function create(CreateResponsibilityRequest $request)
    {
        try {

            // create responsibility
            $responsibility = Responsibility::create([
                'name' => $request->name,
                'role_id' => $request->role_id
            ]);

            if(!$responsibility) {
                throw new Exception('Responsibility creation failed');
            }

            return ResponseFormatter::success($responsibility, 'Responsibility created');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(),500);
        }
    }

    public function destroy($id)
    {
        try {
            // get responsibility
            $responsibility = Responsibility::find($id);

            // Todo: check if responsibility is owned by user

            // 'check' if responsibility exists
            if(!$responsibility) {
                throw new Exception('Responsibility not found');
            }

            // delete responsibility
            $responsibility->delete();

            return ResponseFormatter::success('Responsibility deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
