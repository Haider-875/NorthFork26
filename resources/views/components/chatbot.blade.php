<!-- NORTH FORK AUTO - INTELLIGENT AI AUTO SERVICE CHATBOT -->
<div id="nfa-chatbot-root">
    <!-- Chat Widget Styles (Scoped Automotive CSS with Bootstrap 5.3 standards - Zero Tailwind) -->
    <style>
        #nfa-chatbot-root {
            --nfa-bg-main: #0c0c0f;
            --nfa-bg-card: #15151a;
            --nfa-bg-bubble-ai: #1a1a22;
            --nfa-bg-input: #101015;
            --nfa-border: #292934;
            --nfa-border-subtle: #1e1e28;
            --nfa-primary: #DF1F40;
            --nfa-primary-hover: #fa274d;
            --nfa-primary-glow: rgba(223, 31, 64, 0.4);
            --nfa-text: #f8fafc;
            --nfa-text-muted: #94a3b8;
            --nfa-font-heading: 'Sora', -apple-system, BlinkMacSystemFont, sans-serif;
            --nfa-font-body: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            --nfa-font-mono: 'JetBrains Mono', monospace;
            font-family: var(--nfa-font-body);
            color: var(--nfa-text);
        }

        /* Floating Trigger Launcher */
        .nfa-chat-launcher {
            position: fixed;
            bottom: 24px;
            right: 24px;
            z-index: 1050;
            display: flex;
            align-items: center;
            gap: 12px;
            cursor: pointer;
            transition: transform 0.25s cubic-bezier(0.2, 0.8, 0.2, 1);
            user-select: none;
        }
        @media (max-width: 1024px) {
            .nfa-chat-launcher {
                bottom: 76px; /* Above mobile sticky bottom bar */
                right: 16px;
            }
        }
        .nfa-chat-launcher:hover {
            transform: translateY(-2px);
        }

        .nfa-launcher-pill {
            background: rgba(18, 18, 24, 0.95);
            backdrop-filter: blur(8px);
            border: 1px solid var(--nfa-border);
            padding: 8px 14px;
            border-radius: 9999px;
            display: flex;
            align-items: center;
            gap: 8px;
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.5);
            font-family: var(--nfa-font-mono);
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: var(--nfa-text);
        }
        @media (max-width: 640px) {
            .nfa-launcher-pill {
                display: none;
            }
        }

        .nfa-live-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background-color: #10b981;
            box-shadow: 0 0 10px #10b981;
            animation: nfaPulse 2s infinite;
        }
        @keyframes nfaPulse {
            0% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0.7); }
            70% { transform: scale(1.1); box-shadow: 0 0 0 6px rgba(16, 185, 129, 0); }
            100% { transform: scale(0.95); box-shadow: 0 0 0 0 rgba(16, 185, 129, 0); }
        }

        .nfa-launcher-btn {
            width: 58px;
            height: 58px;
            border-radius: 50%;
            background: linear-gradient(135deg, #1b1b22, #0d0d12);
            border: 2px solid var(--nfa-primary);
            box-shadow: 0 8px 30px var(--nfa-primary-glow), 0 0 0 2px rgba(223, 31, 64, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            transition: all 0.3s ease;
        }
        .nfa-launcher-btn:hover {
            box-shadow: 0 10px 35px rgba(223, 31, 64, 0.6);
            border-color: var(--nfa-primary-hover);
        }
        .nfa-launcher-badge {
            position: absolute;
            top: -2px;
            right: -2px;
            background: var(--nfa-primary);
            color: #fff;
            font-size: 10px;
            font-weight: 800;
            width: 20px;
            height: 20px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px solid var(--nfa-bg-main);
            font-family: var(--nfa-font-mono);
        }

        /* Main Chat Window */
        .nfa-chat-window {
            position: fixed;
            bottom: 96px;
            right: 24px;
            width: 410px;
            max-width: calc(100vw - 32px);
            height: 640px;
            max-height: calc(100vh - 120px);
            background: var(--nfa-bg-main);
            border: 1px solid var(--nfa-border);
            border-radius: 12px;
            box-shadow: 0 24px 60px rgba(0, 0, 0, 0.8), 0 0 0 1px rgba(255, 255, 255, 0.05);
            display: none;
            flex-direction: column;
            overflow: hidden;
            z-index: 1060;
            animation: nfaSlideUp 0.3s cubic-bezier(0.16, 1, 0.3, 1) forwards;
        }
        @media (max-width: 640px) {
            .nfa-chat-window {
                bottom: 0 !important;
                right: 0 !important;
                left: 0 !important;
                width: 100vw !important;
                max-width: 100vw !important;
                height: 100% !important;
                max-height: 100% !important;
                border-radius: 0 !important;
                border: none !important;
            }
        }
        @keyframes nfaSlideUp {
            from { opacity: 0; transform: translateY(18px) scale(0.98); }
            to { opacity: 1; transform: translateY(0) scale(1); }
        }

        /* Chat Header */
        .nfa-chat-header {
            background: linear-gradient(180deg, #181820 0%, #111116 100%);
            border-bottom: 1px solid var(--nfa-border);
            padding: 14px 18px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            user-select: none;
        }
        .nfa-header-info {
            display: flex;
            align-items: center;
            gap: 12px;
        }
        .nfa-header-avatar {
            width: 38px;
            height: 38px;
            border-radius: 8px;
            background: #1e1e28;
            border: 1px solid var(--nfa-primary);
            display: flex;
            align-items: center;
            justify-content: center;
            color: var(--nfa-primary);
        }
        .nfa-header-titles h4 {
            font-family: var(--nfa-font-heading);
            font-size: 14px;
            font-weight: 700;
            color: #ffffff;
            margin: 0;
            line-height: 1.2;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .nfa-header-titles span {
            font-family: var(--nfa-font-mono);
            font-size: 10px;
            color: var(--nfa-text-muted);
            letter-spacing: 0.04em;
        }
        .nfa-header-actions {
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .nfa-action-btn {
            background: transparent;
            border: none;
            color: var(--nfa-text-muted);
            width: 32px;
            height: 32px;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
        }
        .nfa-action-btn:hover {
            background: rgba(255, 255, 255, 0.08);
            color: #fff;
        }

        /* Message Area */
        .nfa-chat-messages {
            flex: 1;
            overflow-y: auto;
            padding: 18px;
            display: flex;
            flex-direction: column;
            gap: 14px;
            background-color: var(--nfa-bg-main);
            background-image: radial-gradient(rgba(223, 31, 64, 0.03) 1px, transparent 0);
            background-size: 24px 24px;
        }
        .nfa-chat-messages::-webkit-scrollbar {
            width: 6px;
        }
        .nfa-chat-messages::-webkit-scrollbar-thumb {
            background: var(--nfa-border);
            border-radius: 3px;
        }

        /* Welcome Card */
        .nfa-welcome-card {
            background: var(--nfa-bg-card);
            border: 1px solid var(--nfa-border);
            border-radius: 10px;
            padding: 16px;
            margin-bottom: 4px;
        }
        .nfa-welcome-header {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 10px;
        }
        .nfa-welcome-badge {
            font-family: var(--nfa-font-mono);
            font-size: 9px;
            text-transform: uppercase;
            background: rgba(223, 31, 64, 0.15);
            color: var(--nfa-primary);
            border: 1px solid rgba(223, 31, 64, 0.3);
            padding: 2px 6px;
            border-radius: 3px;
            font-weight: 700;
        }
        .nfa-welcome-card p {
            font-size: 13px;
            line-height: 1.5;
            color: #cbd5e1;
            margin: 0 0 12px 0;
        }

        /* Suggested Questions */
        .nfa-suggestions {
            display: flex;
            flex-wrap: wrap;
            gap: 6px;
            margin-top: 8px;
        }
        .nfa-suggestion-chip {
            background: #171720;
            border: 1px solid var(--nfa-border);
            color: #cbd5e1;
            font-size: 11px;
            padding: 5px 10px;
            border-radius: 14px;
            cursor: pointer;
            transition: all 0.2s ease;
            text-align: left;
            user-select: none;
        }
        .nfa-suggestion-chip:hover {
            background: #232330;
            border-color: var(--nfa-primary);
            color: #fff;
            transform: translateY(-1px);
        }

        /* Message Bubbles */
        .nfa-message-row {
            display: flex;
            flex-direction: column;
            max-width: 88%;
            animation: nfaFadeIn 0.25s ease forwards;
        }
        @keyframes nfaFadeIn {
            from { opacity: 0; transform: translateY(6px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .nfa-message-row.user {
            align-self: flex-end;
            align-items: flex-end;
        }
        .nfa-message-row.assistant {
            align-self: flex-start;
            align-items: flex-start;
        }

        .nfa-bubble {
            padding: 10px 14px;
            border-radius: 12px;
            font-size: 13px;
            line-height: 1.55;
            word-break: break-word;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.25);
        }
        .nfa-message-row.user .nfa-bubble {
            background: linear-gradient(135deg, #DF1F40, #ad132e);
            color: #ffffff;
            border-bottom-right-radius: 2px;
        }
        .nfa-message-row.assistant .nfa-bubble {
            background: var(--nfa-bg-bubble-ai);
            color: #f1f5f9;
            border: 1px solid var(--nfa-border);
            border-bottom-left-radius: 2px;
        }
        .nfa-bubble strong {
            color: #ffffff;
            font-weight: 700;
        }
        .nfa-bubble a {
            color: #ff6b84;
            text-decoration: underline;
            font-weight: 600;
        }
        .nfa-bubble ul, .nfa-bubble ol {
            margin: 6px 0;
            padding-left: 18px;
        }
        .nfa-bubble li {
            margin-bottom: 4px;
        }

        .nfa-timestamp {
            font-family: var(--nfa-font-mono);
            font-size: 9px;
            color: #64748b;
            margin-top: 4px;
            padding: 0 4px;
        }

        /* Typing Indicator */
        .nfa-typing {
            display: flex;
            align-items: center;
            gap: 5px;
            padding: 12px 16px;
            background: var(--nfa-bg-bubble-ai);
            border: 1px solid var(--nfa-border);
            border-radius: 12px;
            border-bottom-left-radius: 2px;
            width: fit-content;
        }
        .nfa-typing-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background-color: var(--nfa-primary);
            animation: nfaTyping 1.4s infinite ease-in-out both;
        }
        .nfa-typing-dot:nth-child(1) { animation-delay: -0.32s; }
        .nfa-typing-dot:nth-child(2) { animation-delay: -0.16s; }
        @keyframes nfaTyping {
            0%, 80%, 100% { transform: scale(0.6); opacity: 0.4; }
            40% { transform: scale(1.1); opacity: 1; }
        }

        /* Appointment Request Card (Embedded Form inside Chat) */
        .nfa-appointment-card {
            background: #14141c;
            border: 1px solid var(--nfa-primary);
            border-radius: 10px;
            padding: 14px;
            margin-top: 8px;
            width: 100%;
        }
        .nfa-appointment-card h5 {
            font-family: var(--nfa-font-heading);
            font-size: 13px;
            font-weight: 700;
            color: #ffffff;
            margin: 0 0 10px 0;
            display: flex;
            align-items: center;
            gap: 6px;
        }
        .nfa-form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
        }
        .nfa-form-full {
            grid-column: span 2;
        }
        .nfa-form-input {
            width: 100%;
            background: #09090d;
            border: 1px solid var(--nfa-border);
            border-radius: 4px;
            padding: 6px 10px;
            font-size: 12px;
            color: #fff;
            outline: none;
            box-sizing: border-box;
        }
        .nfa-form-input:focus {
            border-color: var(--nfa-primary);
        }
        .nfa-form-label {
            font-family: var(--nfa-font-mono);
            font-size: 9px;
            color: #94a3b8;
            text-transform: uppercase;
            margin-bottom: 2px;
            display: block;
        }
        .nfa-submit-booking-btn {
            background: var(--nfa-primary);
            color: #ffffff;
            border: none;
            border-radius: 4px;
            padding: 8px 12px;
            font-family: var(--nfa-font-heading);
            font-weight: 700;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            cursor: pointer;
            width: 100%;
            margin-top: 10px;
            transition: all 0.2s;
        }
        .nfa-submit-booking-btn:hover {
            background: var(--nfa-primary-hover);
            box-shadow: 0 0 16px var(--nfa-primary-glow);
        }

        /* Input Controls Area */
        .nfa-chat-input-area {
            background: #111117;
            border-top: 1px solid var(--nfa-border);
            padding: 12px 14px;
        }
        .nfa-quick-actions-bar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .nfa-quick-booking-pill {
            background: rgba(223, 31, 64, 0.12);
            border: 1px solid rgba(223, 31, 64, 0.3);
            color: #ff5775;
            font-family: var(--nfa-font-mono);
            font-size: 10px;
            padding: 3px 8px;
            border-radius: 4px;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            align-items: center;
            gap: 4px;
        }
        .nfa-quick-booking-pill:hover {
            background: var(--nfa-primary);
            color: #fff;
        }
        .nfa-char-count {
            font-family: var(--nfa-font-mono);
            font-size: 9px;
            color: #64748b;
        }

        .nfa-input-wrapper {
            display: flex;
            align-items: flex-end;
            background: var(--nfa-bg-input);
            border: 1px solid var(--nfa-border);
            border-radius: 8px;
            padding: 6px 10px;
            transition: border-color 0.2s ease;
        }
        .nfa-input-wrapper:focus-within {
            border-color: var(--nfa-primary);
            box-shadow: 0 0 0 1px var(--nfa-primary);
        }
        .nfa-chat-textarea {
            flex: 1;
            background: transparent;
            border: none;
            color: #ffffff;
            font-size: 13px;
            font-family: var(--nfa-font-body);
            resize: none;
            outline: none;
            max-height: 100px;
            line-height: 1.4;
            padding: 4px 0;
        }
        .nfa-chat-textarea::placeholder {
            color: #64748b;
        }
        .nfa-send-btn {
            background: var(--nfa-primary);
            color: #ffffff;
            border: none;
            border-radius: 6px;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            margin-left: 6px;
            flex-shrink: 0;
        }
        .nfa-send-btn:hover:not(:disabled) {
            background: var(--nfa-primary-hover);
            transform: scale(1.05);
        }
        .nfa-send-btn:disabled {
            background: #2a2a35;
            color: #555566;
            cursor: not-allowed;
        }

        .nfa-disclaimer {
            font-size: 9px;
            color: #64748b;
            text-align: center;
            margin-top: 6px;
            font-family: var(--nfa-font-mono);
        }
        .nfa-disclaimer a {
            color: #94a3b8;
            text-decoration: underline;
        }
    </style>

    <!-- Floating Launcher Trigger Button -->
    <div class="nfa-chat-launcher" id="nfa-launcher" role="button" aria-label="Open North Fork Auto Service Assistant">
        <div class="nfa-launcher-pill">
            <span class="nfa-live-dot"></span>
            <span>AI Auto Assistant</span>
        </div>
        <div class="nfa-launcher-btn">
            <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="text-white">
                <path d="M12 8V4H8"></path>
                <rect width="16" height="12" x="4" y="8" rx="2"></rect>
                <path d="M2 14h2"></path>
                <path d="M20 14h2"></path>
                <path d="M15 13v2"></path>
                <path d="M9 13v2"></path>
            </svg>
            <span class="nfa-launcher-badge" id="nfa-badge">1</span>
        </div>
    </div>

    <!-- Main Chat Window -->
    <div class="nfa-chat-window" id="nfa-window" role="dialog" aria-modal="true" aria-label="North Fork Auto AI Assistant">
        <!-- Header -->
        <div class="nfa-chat-header">
            <div class="nfa-header-info">
                <div class="nfa-header-avatar">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14.7 6.3a1 1 0 0 0 0 1.4l1.6 1.6a1 1 0 0 0 1.4 0l3.77-3.77a6 6 0 0 1-7.94 7.94l-6.91 6.91a2.12 2.12 0 0 1-3-3l6.91-6.91a6 6 0 0 1 7.94-7.94l-3.76 3.76z"></path>
                    </svg>
                </div>
                <div class="nfa-header-titles">
                    <h4>North Fork Auto <span class="nfa-live-dot" style="display:inline-block; width:6px; height:6px;"></span></h4>
                    <span>AI Service Assistant · Talkeetna, AK</span>
                </div>
            </div>
            <div class="nfa-header-actions">
                <button type="button" class="nfa-action-btn" id="nfa-clear-chat" title="Clear conversation" aria-label="Clear conversation">
                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 6h18"></path>
                        <path d="M19 6v14c0 1-1 2-2 2H7c-1 0-2-1-2-2V6"></path>
                        <path d="M8 6V4c0-1 1-2 2-2h4c1 0 2 1 2 2v2"></path>
                    </svg>
                </button>
                <button type="button" class="nfa-action-btn" id="nfa-close-chat" title="Close chat" aria-label="Close chat">
                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="18" y1="6" x2="6" y2="18"></line>
                        <line x1="6" y1="6" x2="18" y2="18"></line>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Messages Container -->
        <div class="nfa-chat-messages" id="nfa-messages">
            <!-- Welcome Card -->
            <div class="nfa-welcome-card">
                <div class="nfa-welcome-header">
                    <span class="nfa-welcome-badge">Official AI Assistant</span>
                    <span style="font-family:var(--nfa-font-mono); font-size:10px; color:#94a3b8;">Mon-Fri 8AM-5PM</span>
                </div>
                <p>Hello! I am your AI Auto Service Assistant at <strong>North Fork Auto</strong> in Talkeetna, Alaska. How can I help your vehicle today?</p>
                
                <div style="font-family:var(--nfa-font-mono); font-size:10px; color:#94a3b8; margin-bottom:6px; text-transform:uppercase;">Quick Questions:</div>
                <div class="nfa-suggestions">
                    <div class="nfa-suggestion-chip" data-query="My check engine light is flashing. Can I still drive?">Check engine light flashing?</div>
                    <div class="nfa-suggestion-chip" data-query="How much does an oil change service cost?">Oil change service & pricing</div>
                    <div class="nfa-suggestion-chip" data-query="Do you service Subaru vehicles and Ford trucks?">Do you service Subaru & Ford?</div>
                    <div class="nfa-suggestion-chip" data-query="I would like to schedule a diagnostic inspection.">Schedule an appointment</div>
                    <div class="nfa-suggestion-chip" data-query="Where are you located and what are your business hours?">Shop hours & location</div>
                </div>
            </div>
        </div>

        <!-- Input Area -->
        <div class="nfa-chat-input-area">
            <div class="nfa-quick-actions-bar">
                <button type="button" class="nfa-quick-booking-pill" id="nfa-trigger-booking">
                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect>
                        <line x1="16" y1="2" x2="16" y2="6"></line>
                        <line x1="8" y1="2" x2="8" y2="6"></line>
                        <line x1="3" y1="10" x2="21" y2="10"></line>
                    </svg>
                    <span>Request Appointment</span>
                </button>
                <span class="nfa-char-count" id="nfa-char-counter">0/500</span>
            </div>

            <form id="nfa-chat-form" onsubmit="return false;">
                <div class="nfa-input-wrapper">
                    <textarea 
                        id="nfa-input" 
                        class="nfa-chat-textarea" 
                        rows="1" 
                        maxlength="500" 
                        placeholder="Ask about repairs, sounds, check engine lights..." 
                        aria-label="Message for North Fork Auto AI Assistant"></textarea>
                    
                    <button type="submit" id="nfa-send" class="nfa-send-btn" title="Send message" aria-label="Send message">
                        <svg width="15" height="15" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
                            <line x1="22" y1="2" x2="11" y2="13"></line>
                            <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                        </svg>
                    </button>
                </div>
            </form>

            <div class="nfa-disclaimer">
                Call <a href="tel:9077333030">(907) 733-3030</a> · After Hours <a href="tel:19072323859">+1 (907) 232-3859</a>
            </div>
        </div>
    </div>

    <!-- Client-Side Chat Engine -->
    <script>
        (function() {
            // Configuration & Session
            const API_ENDPOINT = '/api/chat';
            const APPT_ENDPOINT = '/api/chat/appointment';
            const CLEAR_ENDPOINT = '/api/chat/clear';

            let sessionId = localStorage.getItem('nfa_chat_session_id');
            if (!sessionId) {
                sessionId = 'nfa_' + Math.random().toString(36).substring(2, 9) + '_' + Date.now().toString(36);
                localStorage.setItem('nfa_chat_session_id', sessionId);
            }

            // DOM Elements
            const launcher = document.getElementById('nfa-launcher');
            const chatWindow = document.getElementById('nfa-window');
            const closeBtn = document.getElementById('nfa-close-chat');
            const clearBtn = document.getElementById('nfa-clear-chat');
            const messagesContainer = document.getElementById('nfa-messages');
            const chatForm = document.getElementById('nfa-chat-form');
            const chatInput = document.getElementById('nfa-input');
            const sendBtn = document.getElementById('nfa-send');
            const badge = document.getElementById('nfa-badge');
            const charCounter = document.getElementById('nfa-char-counter');
            const triggerBookingBtn = document.getElementById('nfa-trigger-booking');

            let isThinking = false;

            // Open / Close Handlers
            function toggleChat() {
                const isOpen = chatWindow.style.display === 'flex';
                if (isOpen) {
                    chatWindow.style.display = 'none';
                } else {
                    chatWindow.style.display = 'flex';
                    badge.style.display = 'none';
                    chatInput.focus();
                    scrollToBottom();
                }
            }

            launcher.addEventListener('click', toggleChat);
            closeBtn.addEventListener('click', () => { chatWindow.style.display = 'none'; });

            // Char counter & Textarea auto-resize
            chatInput.addEventListener('input', function() {
                charCounter.textContent = this.value.length + '/500';
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 100) + 'px';
            });

            // Enter key to send (Shift+Enter for newline)
            chatInput.addEventListener('keydown', function(e) {
                if (e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    submitMessage();
                }
            });

            chatForm.addEventListener('submit', function(e) {
                e.preventDefault();
                submitMessage();
            });

            // Suggestion Chips Click
            document.querySelectorAll('.nfa-suggestion-chip').forEach(chip => {
                chip.addEventListener('click', function() {
                    const query = this.getAttribute('data-query');
                    if (query) {
                        chatInput.value = query;
                        submitMessage();
                    }
                });
            });

            // Direct Booking Shortcut
            triggerBookingBtn.addEventListener('click', renderAppointmentCard);

            // Clear Chat
            clearBtn.addEventListener('click', async function() {
                if (confirm('Clear current conversation history?')) {
                    try {
                        await fetch(CLEAR_ENDPOINT, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ session_id: sessionId })
                        });
                    } catch (e) {}

                    localStorage.removeItem('nfa_chat_session_id');
                    sessionId = 'nfa_' + Math.random().toString(36).substring(2, 9) + '_' + Date.now().toString(36);
                    localStorage.setItem('nfa_chat_session_id', sessionId);

                    // Reset messages view
                    const welcomeCard = document.querySelector('.nfa-welcome-card');
                    messagesContainer.innerHTML = '';
                    if (welcomeCard) messagesContainer.appendChild(welcomeCard);
                    appendAssistantBubble("Conversation history cleared. How can I help you today?");
                }
            });

            // Submit User Message
            async function submitMessage() {
                const text = chatInput.value.trim();
                if (!text || isThinking) return;

                // Reset input
                chatInput.value = '';
                chatInput.style.height = 'auto';
                charCounter.textContent = '0/500';

                // Append user bubble
                appendUserBubble(text);
                scrollToBottom();

                // Check for appointment keywords to proactively offer card
                const lower = text.toLowerCase();
                if (lower.includes('schedule') || lower.includes('book') || lower.includes('appointment')) {
                    setTimeout(() => {
                        renderAppointmentCard();
                    }, 400);
                }

                // Show typing indicator
                showTypingIndicator();
                isThinking = true;
                sendBtn.disabled = true;

                try {
                    const response = await fetch(API_ENDPOINT, {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                        },
                        body: JSON.stringify({
                            message: text,
                            session_id: sessionId
                        })
                    });

                    const data = await response.json();
                    hideTypingIndicator();

                    if (data && data.reply) {
                        appendAssistantBubble(data.reply);
                    } else if (data && data.error === 'rate_limit') {
                        appendAssistantBubble(data.reply || "Rate limit reached. Please wait a moment.");
                    } else {
                        appendAssistantBubble("I'm having a momentary connection glitch. Please call our shop directly at (907) 733-3030.");
                    }

                } catch (error) {
                    hideTypingIndicator();
                    console.error('Chat error:', error);
                    appendAssistantBubble("I am unable to reach the server right now. Please call us at (907) 733-3030 for immediate service!");
                } finally {
                    isThinking = false;
                    sendBtn.disabled = false;
                    scrollToBottom();
                }
            }

            // Bubble Helpers
            function appendUserBubble(text) {
                const time = getCurrentTime();
                const row = document.createElement('div');
                row.className = 'nfa-message-row user';
                row.innerHTML = `
                    <div class="nfa-bubble">${escapeHtml(text)}</div>
                    <span class="nfa-timestamp">${time}</span>
                `;
                messagesContainer.appendChild(row);
            }

            function appendAssistantBubble(rawMarkdown) {
                const time = getCurrentTime();
                const formatted = formatMarkdown(rawMarkdown);
                const row = document.createElement('div');
                row.className = 'nfa-message-row assistant';
                row.innerHTML = `
                    <div class="nfa-bubble">${formatted}</div>
                    <span class="nfa-timestamp">${time} · North Fork Auto AI</span>
                `;
                messagesContainer.appendChild(row);
                scrollToBottom();
            }

            // Typing Indicator
            let typingElem = null;
            function showTypingIndicator() {
                if (typingElem) return;
                typingElem = document.createElement('div');
                typingElem.className = 'nfa-message-row assistant';
                typingElem.innerHTML = `
                    <div class="nfa-typing">
                        <span class="nfa-typing-dot"></span>
                        <span class="nfa-typing-dot"></span>
                        <span class="nfa-typing-dot"></span>
                    </div>
                `;
                messagesContainer.appendChild(typingElem);
                scrollToBottom();
            }

            function hideTypingIndicator() {
                if (typingElem) {
                    typingElem.remove();
                    typingElem = null;
                }
            }

            // Render Appointment Request Card
            function renderAppointmentCard() {
                if (document.getElementById('nfa-active-booking-card')) return;

                const cardRow = document.createElement('div');
                cardRow.className = 'nfa-message-row assistant';
                cardRow.id = 'nfa-active-booking-card';

                const tomorrow = new Date();
                tomorrow.setDate(tomorrow.getDate() + 1);
                const minDate = tomorrow.toISOString().split('T')[0];

                cardRow.innerHTML = `
                    <div class="nfa-appointment-card">
                        <h5>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="#DF1F40" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <rect width="18" height="18" x="3" y="4" rx="2" ry="2"></rect>
                                <line x1="16" y1="2" x2="16" y2="6"></line>
                                <line x1="8" y1="2" x2="8" y2="6"></line>
                                <line x1="3" y1="10" x2="21" y2="10"></line>
                            </svg>
                            Request Service Appointment
                        </h5>
                        <form id="nfa-embedded-booking-form">
                            <div class="nfa-form-grid">
                                <div>
                                    <label class="nfa-form-label">Your Name *</label>
                                    <input type="text" name="name" class="nfa-form-input" required placeholder="John Doe">
                                </div>
                                <div>
                                    <label class="nfa-form-label">Phone Number *</label>
                                    <input type="tel" name="phone" class="nfa-form-input" required placeholder="(907) 555-0199">
                                </div>
                                <div>
                                    <label class="nfa-form-label">Email (Optional)</label>
                                    <input type="email" name="email" class="nfa-form-input" placeholder="john@example.com">
                                </div>
                                <div>
                                    <label class="nfa-form-label">Vehicle Year *</label>
                                    <input type="number" name="vehicle_year" class="nfa-form-input" required value="2020" min="1960" max="2027">
                                </div>
                                <div>
                                    <label class="nfa-form-label">Make *</label>
                                    <input type="text" name="vehicle_make" class="nfa-form-input" required placeholder="e.g. Subaru / Ford">
                                </div>
                                <div>
                                    <label class="nfa-form-label">Model *</label>
                                    <input type="text" name="vehicle_model" class="nfa-form-input" required placeholder="e.g. Outback / F-150">
                                </div>
                                <div class="nfa-form-full">
                                    <label class="nfa-form-label">Requested Service *</label>
                                    <select name="service" class="nfa-form-input" required>
                                        <option value="Diagnostic Inspection">Diagnostic Inspection (Check Engine / Scan)</option>
                                        <option value="Brake Repair & Replacement">Brake Repair & Replacement</option>
                                        <option value="Oil & Filter Service">Oil & Filter Service</option>
                                        <option value="Engine Repair & Overhaul">Engine Repair & Overhaul</option>
                                        <option value="Transmission Service">Transmission Service</option>
                                        <option value="Steering & Suspension">Steering & Suspension</option>
                                        <option value="Electrical & Battery">Electrical & Battery</option>
                                        <option value="Cooling & AC Repair">Cooling & AC Repair</option>
                                        <option value="General Inspection">General Alaskan Winter Check</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="nfa-form-label">Preferred Date *</label>
                                    <input type="date" name="preferred_date" class="nfa-form-input" required min="${minDate}" value="${minDate}">
                                </div>
                                <div>
                                    <label class="nfa-form-label">Preferred Time *</label>
                                    <select name="preferred_time" class="nfa-form-input" required>
                                        <option value="08:30 AM">08:30 AM (Early Drop-off)</option>
                                        <option value="10:00 AM" selected>10:00 AM</option>
                                        <option value="01:30 PM">01:30 PM</option>
                                        <option value="03:30 PM">03:30 PM</option>
                                    </select>
                                </div>
                                <div class="nfa-form-full">
                                    <label class="nfa-form-label">Symptoms / Notes</label>
                                    <input type="text" name="notes" class="nfa-form-input" placeholder="e.g. Squeaking noise when braking...">
                                </div>
                            </div>
                            <button type="submit" class="nfa-submit-booking-btn" id="nfa-submit-appt-btn">Send Appointment Request</button>
                        </form>
                    </div>
                `;

                messagesContainer.appendChild(cardRow);
                scrollToBottom();

                // Bind form submission
                const apptForm = document.getElementById('nfa-embedded-booking-form');
                apptForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    const submitBtn = document.getElementById('nfa-submit-appt-btn');
                    submitBtn.disabled = true;
                    submitBtn.textContent = 'Submitting Request...';

                    const formData = new FormData(apptForm);
                    const payload = Object.fromEntries(formData.entries());
                    payload.session_id = sessionId;

                    try {
                        const res = await fetch(APPT_ENDPOINT, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },
                            body: JSON.stringify(payload)
                        });
                        const resData = await res.json();

                        if (resData.success) {
                            cardRow.innerHTML = `
                                <div class="nfa-appointment-card" style="border-color:#10b981; background:rgba(16, 185, 129, 0.08);">
                                    <div style="display:flex; align-items:center; gap:8px; color:#10b981; font-weight:700; font-size:14px; margin-bottom:8px;">
                                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="20 6 9 17 4 12"></polyline></svg>
                                        Appointment Request Received!
                                    </div>
                                    <p style="font-size:12px; margin:0 0 8px 0; color:#e2e8f0;">
                                        Reference Number: <strong style="color:#ffffff; font-family:var(--nfa-font-mono);">${resData.booking_reference}</strong>
                                    </p>
                                    <p style="font-size:11px; margin:0; color:#94a3b8; line-height:1.4;">
                                        Thank you, ${escapeHtml(payload.name)}! Our team will contact you at <strong>${escapeHtml(payload.phone)}</strong> to confirm final bay availability.
                                    </p>
                                </div>
                            `;
                            appendAssistantBubble(`I have filed your service request for your **${payload.vehicle_year} ${payload.vehicle_make} ${payload.vehicle_model}** (${payload.service}) under Reference **${resData.booking_reference}**. Our advisor will confirm availability shortly!`);
                        } else {
                            alert(resData.message || 'Error submitting booking.');
                            submitBtn.disabled = false;
                            submitBtn.textContent = 'Send Appointment Request';
                        }
                    } catch (err) {
                        console.error('Appt error:', err);
                        alert('Could not submit. Please call us at (907) 733-3030.');
                        submitBtn.disabled = false;
                        submitBtn.textContent = 'Send Appointment Request';
                    }
                });
            }

            // Utilities
            function scrollToBottom() {
                messagesContainer.scrollTop = messagesContainer.scrollHeight;
            }

            function getCurrentTime() {
                const now = new Date();
                return now.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            }

            function escapeHtml(str) {
                if (!str) return '';
                return str
                    .replace(/&/g, '&amp;')
                    .replace(/</g, '&lt;')
                    .replace(/>/g, '&gt;')
                    .replace(/"/g, '&quot;')
                    .replace(/'/g, '&#039;');
            }

            function formatMarkdown(text) {
                if (!text) return '';
                let escaped = escapeHtml(text);
                
                // Bold: **text**
                escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
                
                // Italic: *text*
                escaped = escaped.replace(/\*(.*?)\*/g, '<em>$1</em>');
                
                // Bullet points
                escaped = escaped.replace(/(?:^|\n)\* (.*?)(?=(?:\n\* )|\n\n|$)/g, '<li>$1</li>');
                escaped = escaped.replace(/(<li>.*?<\/li>)/gs, '<ul>$1</ul>');
                
                // Line breaks
                escaped = escaped.replace(/\n/g, '<br>');
                
                // Phone link helper
                escaped = escaped.replace(/\((\d{3})\)\s*(\d{3})-(\d{4})/g, '<a href="tel:$1$2$3">($1) $2-$3</a>');

                return escaped;
            }
        })();
    </script>
</div>
