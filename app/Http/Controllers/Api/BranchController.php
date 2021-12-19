<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Validator;
use App\Models\Branch;

class BranchController extends Controller
{
    public function index() {
        $branches = Branch::all();
        if (count($branches)>0) {
            return response([
                'message' => 'Retrieve All Success',
                'data' => $branches
            ], 200);
        }

        return response([
            'message' => 'Empty',
            'data' => null
        ], 400);
    }

    public function show($id) {
        $branch = Branch::find($id);
        if (!is_null($branch)>0) {
            return response([
                'message' => 'Retrieve Branch Success',
                'data' => $branch
            ], 200);
        }

        return response([
            'message' => 'Branch Not Found',
            'data' => null
        ], 404);
    }

    public function store(Request $request) {
        $storeData = $request->all();
        $validate = Validator::make($storeData, [
            'branch_name' => 'required|max:60|unique:branches',
            'location' => 'required|max:255',
            'manager' => 'required|max:60',
            'contact' => 'required|max:255'
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()], 400);

        $branch = Branch::create($storeData);
        return response([
            'message' => 'Add Branch Success',
            'data' => $branch
        ], 200);
    }

    public function destroy($id) {
        $branch = Branch::find($id);
        if(is_null($branch)) {
            return response([
                'message' => 'Branch Not Found',
                'data' => null
            ], 404);
        }

        if($branch->delete()) {
            return response([
                'message' => 'Delete Branch Success',
                'data' => $branch
            ], 200);
        }

        return response([
            'message' => 'Delete Branch Failed',
            'data' => null,
        ], 400);
    }

    public function update(Request $request, $id) {
        $branch = Branch::find($id);
        if(is_null($branch)) {
            return response([
                'message' => 'Branch Not Found',
                'data' => null
            ], 404);
        }

        $updateData = $request->all();
        $validate = Validator::make($updateData, [
            'branch_name' => ['max:60', 'required', Rule::unique('branches')->ignore($branch)],
            'location' => 'required|max:255',
            'manager' => 'required|max:60',
            'contact' => 'required|max:255'
        ]);

        if($validate->fails())
            return response(['message' => $validate->errors()], 400);

        $branch->branch_name = $updateData['branch_name'];
        $branch->location = $updateData['location'];
        $branch->manager = $updateData['manager'];
        $branch->contact = $updateData['contact'];

        if($branch->save()) {
            return response([
                'message' => 'Update Branch Success',
                'data' => $branch
            ], 200);
        }
        return response([
            'message' => 'Update Branch Failed',
            'data' => null,
        ], 400);
    }
}
