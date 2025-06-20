<?php

namespace Callcocam\ReactPapaLeguas\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class TableUpdateController extends Controller
{
    public function update(Request $request)
    {
        $validated = $request->validate([
            'model' => 'required|string',
            'key' => 'required|string',
            'field' => 'required|string',
            'value' => 'nullable',
        ]);

        try {
            $modelClass = $validated['model'];
            $id = $validated['key'];
            $field = $validated['field'];
            $value = $validated['value'];

            if (!class_exists($modelClass)) {
                return response()->json(['message' => 'Model not found.'], 404);
            }

            $model = $modelClass::find($id);

            if (!$model) {
                return response()->json(['message' => 'Record not found.'], 404);
            }

            // Simple validation to prevent mass assignment issues
            // A more robust solution would check if the field is actually fillable
            if (!in_array($field, $model->getFillable())) {
                 return response()->json(['message' => "Field '{$field}' is not fillable."], 403);
            }

            DB::beginTransaction();

            $model->update([$field => $value]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Record updated successfully.',
                'value' => $model->fresh()->$field
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Update failed.', 'error' => $e->getMessage()], 500);
        }
    }
} 