@extends('laratrust::panel.layout')

@section('title', "Edit {$modelKey}")

@section('content')
  <div class="flex flex-col">
    <div class="-my-2 py-2 overflow-x-auto sm:-mx-6 sm:px-6 lg:-mx-8 lg:px-32">
      <form
        method="POST"
        action="{{ route('laratrust.roles-assignment.update', ['roles_assignment' => $user->getKey(), 'model' => $modelKey]) }}"
        class="align-middle inline-block min-w-full shadow overflow-hidden sm:rounded-lg border-b border-gray-200 p-8 bg-white"
        onsubmit="return validateRoles()"
      >
        @csrf
        @method('PUT')

        <!-- Input Name -->
        <label class="block mb-6">
            <span class="text-gray-700 text-lg font-medium">Name</span>
            <input
                class="form-input mt-1 block w-full bg-gray-200 text-gray-600 border border-gray-300 rounded-md p-2"
                name="name"
                placeholder="this-will-be-the-code-name"
                value="{{ $user->name ?? 'The model doesn\'t have a `name` attribute' }}"
                readonly
                autocomplete="off"
            >
        </label>

        <!-- Roles Section -->
        <span class="block text-gray-700 text-lg font-medium mb-2">Roles</span>
        <div class="flex flex-wrap justify-start mb-4">
            @foreach ($roles as $role)
                <label class="inline-flex items-center mr-6 my-2 text-sm" style="flex: 1 0 20%;">
                    <input
                        type="checkbox"
                        name="roles[]"
                        value="{{ $role->getKey() }}"
                        {!! $role->assigned ? 'checked' : '' !!}
                        {!! $role->assigned && !$role->isRemovable ? 'disabled' : '' !!}
                        class="form-checkbox h-4 w-4"
                    >
                    <span class="ml-2 {!! $role->assigned && !$role->isRemovable ? 'text-gray-600' : '' !!}">
                        {{ $role->display_name ?? $role->name }}
                    </span>
                </label>
            @endforeach
        </div>

        <!-- Permissions Section (optional) -->
        @if ($permissions)
            <span class="block text-gray-700 text-lg font-medium mb-2">Permissions</span>
            <div class="flex flex-wrap justify-start mb-4">
                @foreach ($permissions as $permission)
                    <label class="inline-flex items-center mr-6 my-2 text-sm" style="flex: 1 0 20%;">
                        <input
                            type="checkbox"
                            class="form-checkbox h-4 w-4"
                            name="permissions[]"
                            value="{{ $permission->getKey() }}"
                            {!! $permission->assigned ? 'checked' : '' !!}
                        >
                        <span class="ml-2">{{ $permission->display_name ?? $permission->name }}</span>
                    </label>
                @endforeach
            </div>
        @endif

        <!-- Save and Cancel Buttons -->
        <div class="flex justify-end mt-6">
            <a
                href="{{ route('laratrust.roles-assignment.index', ['model' => $modelKey]) }}"
                class="btn btn-red mr-4 px-4 py-2 rounded text-white bg-red-600 hover:bg-red-700 transition"
            >
                Cancel
            </a>
            <button class="btn btn-blue px-6 py-2 rounded text-white bg-blue-600 hover:bg-blue-700 transition" type="submit">
                Save
            </button>
        </div>
    </form>
    </div>
  </div>

  <script>
    function validateRoles() {
      // Ambil semua input checkbox dengan nama 'roles[]'
      const roles = document.querySelectorAll('input[name="roles[]"]:checked');
      
      // Jika tidak ada role yang dipilih, tampilkan pesan error dan batalkan pengiriman form
      if (roles.length === 0) {
        alert('Please select at least one role.');
        return false;  // Mencegah form untuk dikirim
      }
      
      return true;  // Mengizinkan form untuk dikirim
    }
  </script>
@endsection
