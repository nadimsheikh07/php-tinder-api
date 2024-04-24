<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\UserLike;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\Eloquent\Builder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $query = User::query();

        // Apply search filter
        if ($request->has('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                    ->orWhere('email', 'like', "%{$request->search}%");
            });
        }

        // Apply date filter
        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('created_at', [
                $request->start_date,
                $request->end_date
            ]);
        }

        // Apply sorting
        $sortColumn = $request->input('sort_column', 'id');
        $sortDirection = $request->input('sort_direction', 'asc');
        $query->orderBy($sortColumn, $sortDirection);

        // Paginate the results
        $pageSize = $request->input('page_size', 10);
        $users = $query->paginate($pageSize);

        return response()->json($users);
    }

    public function recommendedUsers()
    {
        $recommendedUsers = User::withCount('likes')
            ->orderByDesc('likes_count')
            ->paginate(10);

        return response()->json($recommendedUsers);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return response()->json(['message' => 'User created successfully', 'user' => $user]);
    }

    public function show(User $user)
    {
        return response()->json($user);
    }

    public function update(Request $request, User $user)
    {
        // Validate the request data
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users,email,' . $user->id,
            // Add validation rules for other fields as needed
        ]);

        // Check for validation errors
        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        // Update the user
        $user->update($validator->validated());

        return response()->json(['message' => 'User updated successfully', 'user' => $user]);
    }

    public function destroy(User $user)
    {
        $user->delete();
        return response()->json(['message' => 'User deleted successfully']);
    }

    public function signup(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|unique:users|max:255',
            'password' => 'required|string|min:6|confirmed',
        ]);

        // Create a new user
        $user = User::create([
            'name' => $validatedData['name'],
            'email' => $validatedData['email'],
            'password' => bcrypt($validatedData['password']),
        ]);

        // Optionally, you can automatically sign in the user after signup
        Auth::login($user);

        return response()->json(['message' => 'User signed up successfully', 'user' => $user]);
    }

    public function signin(Request $request)
    {
        // Validate the request data
        $validatedData = $request->validate([
            'email' => 'required|string|email|max:255',
            'password' => 'required|string|min:6',
        ]);

        // Attempt to authenticate the user
        if (Auth::attempt(['email' => $validatedData['email'], 'password' => $validatedData['password']])) {
            // Authentication successful
            $user = Auth::user();
            $token = $user->createToken('YourAppName')->accessToken;

            return response()->json(['message' => 'User signed in successfully', 'user' => $user, 'token' => $token]);
        } else {
            // Authentication failed
            return response()->json(['message' => 'Invalid credentials'], 401);
        }
    }

    public function like($id)
    {
        $user = request()->user(); // Assuming the authenticated user is the one performing the like action
        $user->load('likes');

        $likedUser = User::findOrFail($id);

        // Check if the user has already liked the liked user
        if ($user->likes()->where('liked_user_id', $likedUser->id)->exists()) {
            return response()->json(['message' => 'You have already liked this user.'], 400);
        }

        // Create a new like entry in the database
        UserLike::create([
            'user_id' => $user->id,
            'liked_user_id' => $likedUser->id,
        ]);

        return response()->json(['message' => 'User liked successfully.']);
    }
}
