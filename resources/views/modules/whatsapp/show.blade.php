@extends('modules.whatsapp.index')

@section('chat_content')
    <!-- Chat Header -->
    <div class="p-3 border-bottom d-flex align-items-center justify-content-between bg-white shadow-sm z-1">
        <div class="d-flex align-items-center">
            <div class="me-3">
                <img src="https://ui-avatars.com/api/?name={{ urlencode($activeCustomer->full_name ?? $phone) }}&background=22c55e&color=fff"
                    class="rounded-circle" width="45" alt="Avatar">
            </div>
            <div>
                <h6 class="mb-0 fw-bold text-dark">{{ $activeCustomer->full_name ?? $phone }}</h6>
                <small class="text-success d-flex align-items-center gap-1">
                    <span class="pulse-small"></span> {{ __('ui.whatsapp_online') }}
                </small>
            </div>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-light btn-sm border rounded-circle shadow-none p-2"
                title="{{ __('ui.refresh') ?? 'Refresh' }}">
                <i class="bi bi-arrow-clockwise"></i>
            </button>
            <button class="btn btn-light btn-sm border rounded-circle shadow-none p-2"
                title="{{ __('ui.details') ?? 'Info' }}">
                <i class="bi bi-info-circle"></i>
            </button>
        </div>
    </div>

    <!-- Messages Area -->
    <div class="flex-grow-1 p-4 overflow-y-auto" id="chat-history"
        style="min-height: 0; background-color: #f0f2f5; background-image: url('https://user-images.githubusercontent.com/15075759/28719144-86dc0f70-73b1-11e7-911d-60d70fcded21.png'); background-blend-mode: overlay;">
        <div id="loading-spinner" class="text-center my-3 d-none">
            <div class="spinner-border text-success spinner-border-sm" role="status">
                <span class="visually-hidden">Loading...</span>
            </div>
        </div>

        <div id="messages-container">
            @foreach($messages as $msg)
                <div class="d-flex mb-3 message-row {{ $msg->direction == 'outbound' ? 'justify-content-end' : 'justify-content-start' }}"
                    data-id="{{ $msg->id }}">
                    <div class="message-bubble {{ $msg->direction == 'outbound' ? 'outbound shadow-sm' : 'inbound shadow-sm' }} p-2 px-3 rounded-3 position-relative"
                        style="max-width: 75%;">
                        <p class="mb-1">{!! nl2br(e($msg->message)) !!}</p>
                        <div class="text-end" style="font-size: 0.7rem; opacity: 0.7;">
                            {{ $msg->created_at->timezone(auth()->user()->company->timezone ?? config('app.timezone'))->format('H:i') }}
                            @if($msg->direction == 'outbound')
                                <i class="bi bi-check2-all ms-1"></i>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    <!-- Input Area -->
    <div class="p-3 border-top bg-white">
        @if($canSend)
            <form action="{{ route('whatsapp.chat.send') }}" method="POST" id="chat-form">
                @csrf
                <input type="hidden" name="phone" value="{{ $phone }}">
                <div class="input-group">
                    <button class="btn btn-light border shadow-none px-3" type="button">
                        <i class="bi bi-emoji-smile fs-5"></i>
                    </button>
                    <button class="btn btn-light border shadow-none px-3" type="button">
                        <i class="bi bi-paperclip fs-5"></i>
                    </button>
                    <input type="text" name="message" class="form-control border shadow-none px-3 py-2"
                        placeholder="{{ __('ui.type_message') }}" required autocomplete="off">
                    <button class="btn btn-chat-send text-white px-4 border-0" type="submit">
                        <i class="bi bi-send-fill"></i>
                    </button>
                </div>
            </form>
        @else
            <div class="alert alert-warning border-0 rounded-3 mb-0 d-flex align-items-center gap-3">
                <i class="bi bi-exclamation-triangle-fill fs-4"></i>
                <div>
                    <h6 class="mb-1 fw-bold">{{ __('ui.window_expired_title') }}</h6>
                    <p class="mb-0 small opacity-75">{{ __('ui.window_expired_desc') }}</p>
                </div>
            </div>
        @endif
    </div>

    <style>
        .message-bubble {
            min-width: 60px;
            position: relative;
        }

        .message-bubble.inbound {
            background-color: #ffffff;
            color: #000;
            border-bottom-left-radius: 0 !important;
        }

        .message-bubble.outbound {
            background-color: #dcf8c6;
            color: #000;
            border-bottom-right-radius: 0 !important;
        }

        .btn-chat-send {
            background: linear-gradient(135deg, #22c55e, #06b6d4);
            transition: opacity 0.2s;
        }

        .btn-chat-send:hover {
            opacity: 0.9;
            color: white;
        }

        .pulse-small {
            width: 8px;
            height: 8px;
            background: #22c55e;
            border-radius: 50%;
            display: inline-block;
            box-shadow: 0 0 0 rgba(34, 197, 94, 0.4);
            animation: pulse-small 2s infinite;
        }

        @keyframes pulse-small {
            0% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0.7);
            }

            70% {
                transform: scale(1);
                box-shadow: 0 0 0 5px rgba(34, 197, 94, 0);
            }

            100% {
                transform: scale(0.95);
                box-shadow: 0 0 0 0 rgba(34, 197, 94, 0);
            }
        }
    </style>

    @push('scripts')
        <script>
            // Scroll to bottom of chat securely
            const chatHistory = document.getElementById('chat-history');
            function scrollToBottom() {
                if (!chatHistory) return;

                chatHistory.scrollTop = chatHistory.scrollHeight;

                const messageRows = document.querySelectorAll('.message-row');
                if (messageRows.length > 0) {
                    const lastMessage = messageRows[messageRows.length - 1];
                    lastMessage.scrollIntoView({ behavior: 'auto', block: 'end' });
                }
            }

            if (chatHistory) {
                scrollToBottom();
                // Fallback delays to bypass rendering quirks
                setTimeout(scrollToBottom, 150);
                setTimeout(scrollToBottom, 500);
            }

            // Lazy load logic
            let isLoading = false;
            let allLoaded = false;

            if (chatHistory) {
                chatHistory.addEventListener('scroll', function () {
                    if (chatHistory.scrollTop <= 10 && !isLoading && !allLoaded) {
                        loadOlderMessages();
                    }
                });

                // If the chat history does not overflow (no scrollbar), load older messages proactively
                if (chatHistory.scrollHeight <= chatHistory.clientHeight && !allLoaded) {
                    loadOlderMessages();
                }
            }

            function loadOlderMessages() {
                const firstMessage = document.querySelector('.message-row');
                if (!firstMessage) return;

                const beforeId = firstMessage.getAttribute('data-id');
                const spinner = document.getElementById('loading-spinner');

                isLoading = true;
                spinner.classList.remove('d-none');

                const oldScrollHeight = chatHistory.scrollHeight;

                // Use relative URL to prevent mixed content issues (HTTP vs HTTPS) on Ngrok
                fetch(`/chat/{{ $phone }}/messages?before_id=${beforeId}`)
                    .then(response => {
                        if (!response.ok) throw new Error('Network error');
                        return response.json();
                    })
                    .then(data => {
                        const messagesContainer = document.getElementById('messages-container');
                        spinner.classList.add('d-none');

                        if (data.messages.length === 0) {
                            allLoaded = true;
                        } else {
                            let htmlString = '';
                            data.messages.forEach(msg => {
                                const justifyClass = msg.direction === 'outbound' ? 'justify-content-end' : 'justify-content-start';
                                const bubbleClass = msg.direction === 'outbound' ? 'outbound shadow-sm' : 'inbound shadow-sm';
                                const checkIcon = msg.direction === 'outbound' ? '<i class="bi bi-check2-all ms-1"></i>' : '';

                                htmlString += `
                                    <div class="d-flex mb-3 message-row ${justifyClass}" data-id="${msg.id}">
                                        <div class="message-bubble ${bubbleClass} p-2 px-3 rounded-3 position-relative" style="max-width: 75%;">
                                            <p class="mb-1">${msg.message}</p>
                                            <div class="text-end" style="font-size: 0.7rem; opacity: 0.7;">
                                                ${msg.time} ${checkIcon}
                                            </div>
                                        </div>
                                    </div>
                                `;
                            });

                            messagesContainer.insertAdjacentHTML('afterbegin', htmlString);
                            // Adjust scrollTop keeping previous messages identical pixel position
                            chatHistory.scrollTop = chatHistory.scrollHeight - oldScrollHeight;
                        }

                        isLoading = false;
                    })
                    .catch(error => {
                        console.error("Error loading messages", error);
                        spinner.classList.add('d-none');
                        isLoading = false;
                    });
            }

            // Handle form submission to prevent duplicate clicks
            const chatForm = document.getElementById('chat-form');
            if (chatForm) {
                chatForm.addEventListener('submit', function () {
                    this.querySelector('button[type="submit"]').disabled = true;
                });
            }
        </script>
    @endpush
@endsection