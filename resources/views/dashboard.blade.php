@extends('shopify-app::layouts.default')

@section('content')
    <div class="container mx-auto px-4 py-8">
        <h1 class="text-3xl font-bold mb-8">Welcome to Magic Sync Master</h1>
        <p class="mb-4">You are: {{ $shopDomain }}</p>

        <div class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
            <h2 class="text-xl font-semibold mb-4">Your Connection ID</h2>
            <p class="text-gray-700 text-lg mb-4">{{ $connection->connection_id }}</p>
            
            @if($connection->connected_to)
                <button id="disconnectBtn" class="bg-red-500 hover:bg-red-700 text-white font-bold py-2 px-4 rounded">
                    Disconnect
                </button>
            @else
                <button id="connectBtn" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
                    Connect
                </button>
            @endif
        </div>

        @if($connection->connected_to)
            <div id="connectedStoreInfo" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                <h2 class="text-xl font-semibold mb-4">Connected Store</h2>
                <p class="text-gray-700 text-lg">{{ $connection->connected_to }}</p>
            </div>
        @endif
    </div>

    <div id="connectModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 hidden overflow-y-auto h-full w-full">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <h3 class="text-lg font-semibold mb-4">Enter Connection ID</h3>
            <input type="text" id="connectionIdInput" class="w-full px-3 py-2 border rounded-md" placeholder="Connection ID">
            <div class="mt-4 flex justify-end">
                <button id="submitConnectionId" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded mr-2">
                    Submit
                </button>
                <button id="closeModal" class="bg-gray-300 hover:bg-gray-400 text-black font-bold py-2 px-4 rounded">
                    Close
                </button>
            </div>
        </div>
    </div>
@endsection

@section('scripts')
    @parent
    <script src="https://unpkg.com/@shopify/app-bridge@3"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        var AppBridge = window['app-bridge'];
        var actions = AppBridge.actions;
        var TitleBar = actions.TitleBar;
        var Button = actions.Button;
        var Redirect = actions.Redirect;
        var Toast = actions.Toast;

        var app = AppBridge.createApp({
            apiKey: '{{ config('shopify-app.api_key') }}',
            host: '{{ request()->get('host') }}',
            forceRedirect: true
        });

        var toastNotice = Toast.create(app, {
            message: '',
            duration: 5000,
        });

        actions.TitleBar.create(app, { title: 'Magic Sync Master' });

        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        });

        $(document).ready(function() {
            $('#connectBtn').click(function() {
                $('#connectModal').removeClass('hidden');
            });

            $('#closeModal').click(function() {
                $('#connectModal').addClass('hidden');
            });

            $('#submitConnectionId').click(function() {
                const connectionId = $('#connectionIdInput').val();
                $.ajax({
                    url: '{{ route("connect") }}',
                    method: 'POST',
                    data: { connection_id: connectionId },
                    success: function(response) {
                        $('#connectModal').addClass('hidden');
                        toastNotice.update({ message: 'Successfully connected!', isError: false });
                        toastNotice.dispatch(Toast.Action.SHOW);
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        toastNotice.update({ message: 'Error: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'An unknown error occurred'), isError: true });
                        toastNotice.dispatch(Toast.Action.SHOW);
                    }
                });
            });

            $('#disconnectBtn').click(function() {
                $.ajax({
                    url: '{{ route("disconnect") }}',
                    method: 'POST',
                    success: function(response) {
                        toastNotice.update({ message: 'Successfully disconnected!', isError: false });
                        toastNotice.dispatch(Toast.Action.SHOW);
                        location.reload();
                    },
                    error: function(xhr, status, error) {
                        toastNotice.update({ message: 'Error occurred while disconnecting: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'An unknown error occurred'), isError: true });
                        toastNotice.dispatch(Toast.Action.SHOW);
                    }
                });
            });
        });
    </script>
@endsection