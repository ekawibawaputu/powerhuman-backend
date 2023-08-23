<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Http\Requests\CreateTeamRequest;
use App\Http\Requests\UpdateTeamRequest;
use App\Models\Team;
use Exception;
use Illuminate\Http\Request;

class TeamController extends Controller
{
    public function fetch (Request $request)
    {
        $id = $request->input('id');
        $name = $request->input('name');
        $limit = $request->input('limit');

        $teamQuery = Team::query();

        // get single team
        if($id){
            $team = $teamQuery->find($id);

            if($team){
                return ResponseFormatter::success($team, 'Team found');
            }
            return ResponseFormatter::error('Team not found', 404);
        }

        // get multiple teams
        $teams = $teamQuery->where('company_id', $request->company_id);

        if($name){
            $teams->where('name','like','%',$name,'%');
        }
        
        return ResponseFormatter::success([
            $teams->paginate($limit),
            'Team found'
        ]);
    }

    public function create(CreateTeamRequest $request)
    {
        try {
            // upload icon
            if($request->hasFile('icon')) {
                $path = $request->file('icon')->store('public/icons');
            }

            // create team
            $team = Team::create([
                'name' => $request->name,
                'icon' => $path,
                'company_id' => $request->company_id
            ]);

            if(!$team) {
                throw new Exception('Team creation failed');
            }

            return ResponseFormatter::success($team, 'Team created');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(),500);
        }
    }

    public function update(UpdateTeamRequest $request, $id)
    {
        try {
            // Get team
            $team = Team::find($id);

            // Check if team exists
            if(!$team) {
                throw new Exception('Team creation failed');
            }

            // update icon
            if($request->hasFile('icon')) {
                $path = $request->file('icon')->store('public/icons');
            }

            // update team
            $team->update([
                'name' => $request->name,
                'icon' => isset($path) ? $path : $team->icon,
                'company_id' => $request->company_id
            ]);

            return ResponseFormatter::success($team,'Team updated');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage()); 
        }
    }

    public function destroy($id)
    {
        try {
            // get team
            $team = Team::find($id);

            // Todo: check if team is owned by user

            // 'check' if team exists
            if(!$team) {
                throw new Exception('Team not found');
            }

            // delete team
            $team->delete();

            return ResponseFormatter::success('Team deleted');
        } catch (Exception $e) {
            return ResponseFormatter::error($e->getMessage(), 500);
        }
    }
}
