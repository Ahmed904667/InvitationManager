@extends('layouts.organizer')

@section('title', 'WhatsApp Delivery Status - ' . $event->name)

@section('content')
<div class="container mx-auto px-4 py-8">
    <div class="mb-6">
        <a href="{{ route('organizer.events.update-sent', $event) }}" class="text-blue-600 hover:text-blue-800">
            <i class="fas fa-arrow-left mr-2"></i>Back to Event
        </a>
        <h1 class="text-3xl font-bold text-gray-900 mt-4">WhatsApp Delivery Status</h1>
        <p class="text-gray-600 mt-2">Track the real-time delivery status of your WhatsApp messages for {{ $event->name }}</p>
    </div>

    <!-- Statistics Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-blue-100 rounded-lg">
                    <i class="fab fa-whatsapp text-blue-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Total WhatsApp</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['total'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-green-100 rounded-lg">
                    <i class="fas fa-check-circle text-green-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Delivered</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['delivered'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-yellow-100 rounded-lg">
                    <i class="fas fa-clock text-yellow-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Pending</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['pending'] }}</p>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-lg shadow p-6">
            <div class="flex items-center">
                <div class="p-2 bg-red-100 rounded-lg">
                    <i class="fas fa-exclamation-circle text-red-600 text-xl"></i>
                </div>
                <div class="ml-4">
                    <p class="text-sm font-medium text-gray-600">Failed</p>
                    <p class="text-2xl font-bold text-gray-900">{{ $stats['failed'] }}</p>
                </div>
            </div>
        </div>
    </div>

    <!-- WhatsApp Notifications -->
    @if(isset($notifications['whatsapp']) && count($notifications['whatsapp']) > 0)
    <div class="bg-white rounded-lg shadow">
        <div class="px-6 py-4 border-b border-gray-200">
            <h2 class="text-lg font-semibold text-gray-900 flex items-center">
                <i class="fab fa-whatsapp text-green-500 mr-2"></i>
                WhatsApp Message Status
            </h2>
            <p class="text-sm text-gray-600 mt-1">Real-time status updates from Twilio</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Guest</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Twilio Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Sent At</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Message ID</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @foreach($notifications['whatsapp'] as $notification)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <div class="text-sm font-medium text-gray-900">{{ $notification->guest->name }}</div>
                            <div class="text-sm text-gray-500">{{ $notification->guest->phone }}</div>
                        </td>
                        <td class="px-6 py-4">
                            <div class="text-sm text-gray-900 max-w-xs truncate">{{ $notification->message }}</div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @switch($notification->status)
                                @case('pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        Pending
                                    </span>
                                    @break
                                @case('sent')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        Sent
                                    </span>
                                    @break
                                @case('delivered')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        Delivered
                                    </span>
                                    @break
                                @case('failed')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        Failed
                                    </span>
                                    @break
                                @default
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                        {{ ucfirst($notification->status) }}
                                    </span>
                            @endswitch
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @if($notification->delivery_details && isset($notification->delivery_details['twilio_status']))
                                <span class="text-sm text-gray-900">{{ ucfirst($notification->delivery_details['twilio_status']) }}</span>
                            @else
                                <span class="text-sm text-gray-500">-</span>
                            @endif
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $notification->created_at->format('M j, Y g:i A') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($notification->external_id)
                                <span class="text-xs font-mono bg-gray-100 px-2 py-1 rounded">{{ $notification->external_id }}</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @else
    <div class="bg-white rounded-lg shadow p-6">
        <div class="text-center">
            <i class="fab fa-whatsapp text-gray-300 text-4xl mb-4"></i>
            <h3 class="text-lg font-medium text-gray-900 mb-2">No WhatsApp Notifications</h3>
            <p class="text-gray-500">You haven't sent any WhatsApp notifications for this event yet.</p>
            <p class="text-sm text-gray-400 mt-2">WhatsApp messages will appear here after you remove guests with WhatsApp notifications enabled.</p>
        </div>
    </div>
    @endif

    <!-- Info Box -->
    <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mt-6">
        <div class="flex">
            <div class="flex-shrink-0">
                <i class="fas fa-info-circle text-blue-400"></i>
            </div>
            <div class="ml-3">
                <h3 class="text-sm font-medium text-blue-800">How WhatsApp Delivery Tracking Works</h3>
                <div class="mt-2 text-sm text-blue-700">
                    <p>• <strong>Pending:</strong> Message sent to Twilio, waiting for status update</p>
                    <p>• <strong>Sent:</strong> Twilio confirmed message was sent to recipient</p>
                    <p>• <strong>Delivered:</strong> Message successfully delivered to recipient's WhatsApp</p>
                    <p>• <strong>Failed:</strong> Message could not be delivered (invalid number, blocked, etc.)</p>
                </div>
                <p class="mt-2 text-xs text-blue-600">
                    Status updates are received in real-time via Twilio webhooks. Refresh this page to see the latest status.
                </p>
            </div>
        </div>
    </div>
</div>
@endsection
