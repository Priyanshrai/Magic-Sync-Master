@extends('shopify-app::layouts.default')

@section('content')
<div class="max-w-4xl mx-auto p-6">
    <h1 class="text-3xl font-bold text-gray-800 mb-6">Welcome to Magic Sync Master</h1>
    
    <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-8">
        <div class="p-6">
            <h2 class="text-2xl font-semibold text-gray-700 mb-4">How It Works</h2>
            <ol class="list-decimal list-inside space-y-2 text-gray-600">
                <li>Your unique Connection ID is displayed below.</li>
                <li>To connect with another store, click the "Connect" button.</li>
                <li>Enter the Connection ID of the store you want to connect with.</li>
                <li>Once connected, your products, customers, and orders will sync automatically.</li>
            </ol>
        </div>
    </div>

    <div class="bg-white shadow-lg rounded-lg overflow-hidden mb-8">
        <div class="p-6">
            <h2 class="text-xl font-semibold text-gray-700 mb-2">Your Store</h2>
            <p class="text-gray-600 mb-4">{{ $shopDomain }}</p>

            <h2 class="text-xl font-semibold text-gray-700 mb-2">Your Connection ID</h2>
            <p class="text-2xl font-mono bg-gray-100 p-3 rounded mb-4">{{ $connection->connection_id }}</p>
            
            @if($connection->connected_to)
                <div class="mb-4">
                    <h2 class="text-xl font-semibold text-gray-700 mb-2">Connected Store</h2>
                    <p class="text-gray-600">{{ $connection->connected_to }}</p>
                </div>
                <button id="disconnectBtn" class="bg-red-500 hover:bg-red-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                    Disconnect
                </button>
            @else
                <button id="connectBtn" class="bg-blue-500 hover:bg-blue-600 text-white font-bold py-2 px-4 rounded transition duration-300">
                    Connect to Another Store
                </button>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
@parent
<script src="https://unpkg.com/@shopify/app-bridge@3"></script>
<script src="https://unpkg.com/@shopify/app-bridge-utils@3"></script>
<script>
    var AppBridge = window['app-bridge'];
    var actions = AppBridge.actions;
    var utils = window['app-bridge-utils'];

    var app = AppBridge.createApp({
        apiKey: '{{ config('shopify-app.api_key') }}',
        host: '{{ request()->get('host') }}',
        forceRedirect: true
    });

    var modalOptions = {
        title: 'Connect to Another Store',
        message: 'Enter the Connection ID of the store you want to connect with:',
        inputType: 'text',
        inputPlaceholder: 'Connection ID',
        primaryAction: {
            content: 'Connect',
            onAction: submitConnectionId
        },
        secondaryActions: [{
            content: 'Cancel',
            onAction: () => modalComponent.dispatch(AppBridge.actions.Modal.Action.CLOSE)
        }]
    };

    var modalComponent = actions.Modal.create(app, modalOptions);

    function showToast(message, isError = false) {
        const toastOptions = {
            message: message,
            duration: 5000,
            isError: isError
        };
        const toastNotice = actions.Toast.create(app, toastOptions);
        toastNotice.dispatch(actions.Toast.Action.SHOW);
    }

    function submitConnectionId() {
        const connectionId = modalComponent.getData()['0'];
        utils.getSessionToken(app).then((token) => {
            $.ajax({
                url: '{{ route("connect") }}',
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token
                },
                data: { connection_id: connectionId },
                success: function(response) {
                    modalComponent.dispatch(actions.Modal.Action.CLOSE);
                    showToast('Successfully connected!');
                    location.reload();
                },
                error: function(xhr, status, error) {
                    showToast('Error: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'An unknown error occurred'), true);
                }
            });
        });
    }

    document.getElementById('connectBtn')?.addEventListener('click', function() {
        modalComponent.dispatch(actions.Modal.Action.OPEN);
    });

    document.getElementById('disconnectBtn')?.addEventListener('click', function() {
        utils.getSessionToken(app).then((token) => {
            $.ajax({
                url: '{{ route("disconnect") }}',
                method: 'POST',
                headers: {
                    'Authorization': 'Bearer ' + token
                },
                success: function(response) {
                    showToast('Successfully disconnected!');
                    location.reload();
                },
                error: function(xhr, status, error) {
                    showToast('Error occurred while disconnecting: ' + (xhr.responseJSON ? xhr.responseJSON.error : 'An unknown error occurred'), true);
                }
            });
        });
    });
</script>
@endsection