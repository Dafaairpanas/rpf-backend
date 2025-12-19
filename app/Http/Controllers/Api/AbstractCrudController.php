<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

abstract class AbstractCrudController extends Controller
{
    // Override di child
    protected string $modelClass;

    protected array $rules = [];

    protected function model(): Model
    {
        return new ($this->modelClass);
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);
        $query = $this->model()->newQuery();

        if ($request->boolean('with_trashed')) {
            $query = $query->withTrashed();
        }

        // optional simple search built on 'name' or 'title'
        if ($q = $request->get('q')) {
            $query->where(function ($qr) use ($q) {
                $qr->where('name', 'like', "%{$q}%")
                    ->orWhere('title', 'like', "%{$q}%");
            });
        }

        $data = $query->latest()->paginate($perPage);

        return response()->json(['success' => true, 'data' => $data]);
    }

    public function store(Request $request)
    {
        $payload = $request->validate($this->rules);
        $payload = $this->prepareData($payload, $request);

        $model = $this->model()->create($payload);

        return response()->json(['success' => true, 'data' => $model], 201);
    }

    public function show($id)
    {
        $model = $this->model()->findOrFail($id);

        return response()->json(['success' => true, 'data' => $model]);
    }

    public function update(Request $request, $id)
    {
        $model = $this->model()->findOrFail($id);
        $payload = $request->validate($this->rulesForUpdate());
        $payload = $this->prepareData($payload, $request, $model);

        $model->update($payload);

        return response()->json(['success' => true, 'data' => $model]);
    }

    public function destroy($id)
    {
        $this->model()->findOrFail($id)->delete();

        return response()->json(['success' => true, 'message' => 'Deleted (soft)']);
    }

    public function restore($id)
    {
        $model = $this->model()->withTrashed()->findOrFail($id);
        $model->restore();

        return response()->json(['success' => true, 'message' => 'Restored']);
    }

    public function forceDelete($id)
    {
        $model = $this->model()->withTrashed()->findOrFail($id);
        $model->forceDelete();

        return response()->json(['success' => true, 'message' => 'Permanently deleted']);
    }

    // override if need preprocessing (e.g. hash password)
    protected function prepareData(array $payload, Request $request, $model = null): array
    {
        return $payload;
    }

    // default rules for update: make everything nullable except required on store.
    protected function rulesForUpdate(): array
    {
        $rules = [];
        foreach ($this->rules as $k => $v) {
            // allow nullable + keep existing validation where appropriate
            $rules[$k] = str_contains($v, 'required') ? str_replace('required', 'sometimes|required', $v) : 'sometimes|'.$v;
        }

        return $rules;
    }
}
