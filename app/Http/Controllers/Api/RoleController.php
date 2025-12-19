<?php

namespace App\Http\Controllers\Api;

use App\Helpers\ApiResponse;
use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class RoleController extends Controller
{
    /**
     * List all roles with pagination
     */
    public function index(Request $request)
    {
        try {
            $perPage = (int) $request->get('per_page', 20);

            $query = Role::query()->orderBy('name', 'asc');

            // Search by name
            if ($request->filled('q')) {
                $search = $request->q;
                $query->where('name', 'like', "%{$search}%");
            }

            $roles = $query->paginate($perPage);

            return ApiResponse::success($roles);
        } catch (\Exception $e) {
            Log::error('RoleController@index error: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);

            return ApiResponse::error(
                'Gagal mengambil data roles: ' . $e->getMessage(),
                500
            );
        }
    }

    /**
     * Create a new role
     */
    public function store(Request $request)
    {
        try {
            $data = $request->validate([
                'name' => 'required|string|max:50|unique:roles,name'
            ]);

            $role = Role::create($data);

            return ApiResponse::success($role, 'Role berhasil dibuat', 201);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            Log::error('RoleController@store error: ' . $e->getMessage());
            return ApiResponse::error('Gagal membuat role: ' . $e->getMessage(), 500);
        }
    }

    /**
     * Show single role
     */
    public function show($id)
    {
        try {
            $role = Role::findOrFail($id);
            return ApiResponse::success($role);
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::notFound('Role tidak ditemukan');
        } catch (\Exception $e) {
            Log::error('RoleController@show error: ' . $e->getMessage());
            return ApiResponse::error('Gagal mengambil detail role', 500);
        }
    }

    /**
     * Update existing role
     */
    public function update(Request $request, $id)
    {
        try {
            $role = Role::findOrFail($id);

            $data = $request->validate([
                'name' => "required|string|max:50|unique:roles,name,{$id}"
            ]);

            $role->update($data);

            return ApiResponse::success($role, 'Role berhasil diperbarui');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::notFound('Role tidak ditemukan');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return ApiResponse::validationError($e->errors());
        } catch (\Exception $e) {
            Log::error('RoleController@update error: ' . $e->getMessage());
            return ApiResponse::error('Gagal memperbarui role', 500);
        }
    }

    /**
     * Delete role
     */
    public function destroy($id)
    {
        try {
            $role = Role::findOrFail($id);

            // Check if role is being used by any users
            if ($role->users()->count() > 0) {
                return ApiResponse::error(
                    'Role tidak dapat dihapus karena masih digunakan oleh ' . $role->users()->count() . ' user',
                    422
                );
            }

            $role->delete();

            return ApiResponse::success(null, 'Role berhasil dihapus');
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return ApiResponse::notFound('Role tidak ditemukan');
        } catch (\Exception $e) {
            Log::error('RoleController@destroy error: ' . $e->getMessage());
            return ApiResponse::error('Gagal menghapus role', 500);
        }
    }
}
