class AiAssistant {
    constructor() {
        this.isOpen = false;
        this.isAuthenticated = false;
        this.remainingQuestions = 3;
        this.isUnlimited = false;
        this.dailyUsed = 0;
        this.dailyLimit = null;
        this.allowFileUpload = false;
        this.selectedFiles = [];
        this.historyLoaded = false;
        // CTA "Daftar / Login untuk Lanjut Chatting" yang dikirim server saat
        // kuota tamu habis. Disimpan agar tidak dirender berulang kali.
        this.pendingCta = null;
        this.ctaShown = false;
        this.init();
    }

    init() {
        this.createChatWidget();
        this.attachEventListeners();
        this.checkUserLimit();
    }

    createChatWidget() {
        const html = `
            <div class="ai-assistant-container">
                <button class="ai-float-button" id="aiFloatBtn">
                    <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path d="M12 2C6.48 2 2 6.48 2 12c0 1.54.36 3 .97 4.29L2 22l5.71-.97C9 21.64 10.46 22 12 22c5.52 0 10-4.48 10-10S17.52 2 12 2zm0 18c-1.38 0-2.69-.29-3.88-.81l-.28-.14-2.89.49.49-2.89-.14-.28C4.29 14.69 4 13.38 4 12c0-4.41 3.59-8 8-8s8 3.59 8 8-3.59 8-8 8z"/>
                        <circle cx="9" cy="12" r="1.5"/>
                        <circle cx="15" cy="12" r="1.5"/>
                    </svg>
                    <span class="ai-notification-badge" id="aiNotificationBadge" style="display: none;">AI</span>
                </button>

                <div class="ai-chat-window" id="aiChatWindow">
                    <div class="ai-chat-header">
                        <div class="ai-header-info">
                            <div class="ai-avatar"></div>
                            <div class="ai-header-text">
                                <h3>Mersy AI Assistant</h3>
                                <p>Online</p>
                            </div>
                        </div>
                        <button class="ai-close-btn" id="aiCloseBtn">&times;</button>
                    </div>

                    <div class="ai-chat-body" id="aiChatBody">
                        <div class="ai-message">
                            <div class="ai-message-avatar robot"></div>
                            <div class="ai-message-content">
                                <div class="ai-message-bubble">
                                    Halo! Aku Mersy AI Assistant 👋 Asisten belajarmu di MersifLab - platform belajar seputar teknologi pada bidang IoT, VR, AI, Website Development, dan masih banyak lagi. Ada yang bisa aku bantu?
                                </div>
                                <div class="ai-message-time">${this.formatTime(new Date())}</div>
                            </div>
                        </div>
                        <div class="ai-suggestions">
                            <button class="ai-suggestion-btn" data-question="Apa itu MersifLab dan apa yang bisa saya pelajari di sini?">
                                Apa itu MersifLab dan apa yang bisa saya pelajari di sini?
                            </button>
                            <button class="ai-suggestion-btn" data-question="Bagaimana cara bergabung dengan program pelatihan di MersifLab?">
                                Bagaimana cara bergabung dengan program pelatihan di MersifLab?
                            </button>
                            <button class="ai-suggestion-btn" data-question="Apa saja layanan dan fitur yang tersedia di LMS MersifLab?">
                                Apa saja layanan dan fitur yang tersedia di LMS MersifLab?
                            </button>
                        </div>
                    </div>

                    <div class="ai-chat-footer">
                        <div class="ai-limit-warning" id="aiLimitWarning" style="display: none;">
                            ⚠️ <span id="aiLimitText"></span>
                        </div>
                        
                        <!-- File preview area -->
                        <div id="aiFilePreview" class="ai-file-preview" style="display:none;"></div>
                        
                        <div class="ai-input-wrapper">
                            <div class="ai-upload-wrapper" style="display: none;">
                                <button class="ai-upload-btn" id="aiUploadBtn" title="Attach files">
                                    <i class="fas fa-paperclip"></i>
                                </button>
                                <input type="file" id="aiFileInput" name="files[]" multiple accept="image/*,.pdf,.doc,.docx,.txt" style="display:none;" />
                            </div>
                            <textarea 
                                id="aiMessageInput" 
                                placeholder="Tanyakan sesuatu..." 
                                rows="1"
                            ></textarea>
                            <button class="ai-send-btn" id="aiSendBtn">
                                <svg viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M2.01 21L23 12 2.01 3 2 10l15 2-15 2z"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', html);
    }

    attachEventListeners() {
        document.getElementById('aiFloatBtn').addEventListener('click', () => this.toggleChat());
        document.getElementById('aiCloseBtn').addEventListener('click', () => this.toggleChat());
        document.getElementById('aiSendBtn').addEventListener('click', () => this.sendMessage());
        
        // Upload button handler
        document.addEventListener('click', (e) => {
            if (e.target && (e.target.id === 'aiUploadBtn' || e.target.closest && e.target.closest('#aiUploadBtn'))) {
                e.preventDefault();
                const input = document.getElementById('aiFileInput');
                if (input) input.click();
            }
        });
        
        const input = document.getElementById('aiMessageInput');
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.sendMessage();
            }
        });

        input.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = Math.min(this.scrollHeight, 100) + 'px';
        });

        // File input change
        document.addEventListener('change', (e) => {
            if (e.target && e.target.id === 'aiFileInput') {
                const input = e.target;
                const files = Array.from(input.files || []);
                this.selectedFiles = files;
                this.updateFilePreview();
            }
        });

        // Suggestion buttons
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('ai-suggestion-btn')) {
                const question = e.target.dataset.question;
                if (question) {
                    document.getElementById('aiMessageInput').value = question;
                    this.sendMessage();
                }
            }
        });

        // Remove file preview
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('ai-file-remove')) {
                const index = parseInt(e.target.dataset.index);
                this.selectedFiles.splice(index, 1);
                this.updateFilePreview();
                
                // Reset file input if no files left
                if (this.selectedFiles.length === 0) {
                    const fileInput = document.getElementById('aiFileInput');
                    if (fileInput) fileInput.value = null;
                }
            }
        });
    }

    toggleChat() {
        this.isOpen = !this.isOpen;
        const chatWindow = document.getElementById('aiChatWindow');
        const floatButton = document.getElementById('aiFloatBtn');
        
        chatWindow.classList.toggle('active');
        
        if (this.isOpen) {
            if (!this.historyLoaded) {
                this.loadChatHistory();
            }
            
            floatButton.style.opacity = '0';
            floatButton.style.pointerEvents = 'none';
            floatButton.style.transform = 'scale(0)';
            
            document.getElementById('aiMessageInput').focus();
            this.scrollToBottom();
        } else {
            setTimeout(() => {
                floatButton.style.opacity = '1';
                floatButton.style.pointerEvents = 'auto';
                floatButton.style.transform = 'scale(1)';
            }, 100);
        }
    }

    async checkUserLimit() {
        try {
            const response = await fetch('/ai-assistant/check-limit', {
                headers: { 'Accept': 'application/json' }
            });

            // Server bermasalah: widget tetap tampil normal dengan nilai
            // default, tanpa memunculkan error mentah ke pengguna.
            if (!response.ok) {
                console.warn('AI Assistant: check-limit responded', response.status);
                return;
            }

            const data = await this.safeJson(response);
            if (!data) return;

            this.isAuthenticated = data.is_authenticated;
            this.remainingQuestions = data.remaining_questions;
            this.isUnlimited = data.is_unlimited || false;
            this.allowFileUpload = data.allow_file_upload || false;
            this.dailyUsed = data.daily_used || 0;
            this.dailyLimit = data.daily_limit || null;
            this.pendingCta = data.cta || null;

            this.updateLimitWarning();
            this.updateUploadUI();
        } catch (error) {
            console.error('Error checking limit:', error);
        }
    }

    /**
     * Parse JSON tanpa melempar error kalau server membalas HTML
     * (mis. halaman error 500 atau redirect login).
     */
    async safeJson(response) {
        try {
            return await response.json();
        } catch (error) {
            console.warn('AI Assistant: respons bukan JSON yang valid', error);
            return null;
        }
    }

    updateUploadUI() {
        const uploadWrapper = document.querySelector('.ai-upload-wrapper');
        const filePreview = document.getElementById('aiFilePreview');
        const headerText = document.querySelector('.ai-header-text p');
        
        if (!uploadWrapper) return;

        if (this.allowFileUpload) {
            uploadWrapper.style.display = 'flex';
            if (headerText) {
                headerText.textContent = 'Online • Premium Features Enabled';
            }
        } else {
            uploadWrapper.style.display = 'none';
            if (filePreview) {
                filePreview.style.display = 'none';
                filePreview.innerHTML = '';
            }
            this.selectedFiles = [];
            const input = document.getElementById('aiFileInput');
            if (input) input.value = null;
            
            if (headerText) {
                headerText.textContent = 'Online';
            }
        }
    }

    updateFilePreview() {
        const previewDiv = document.getElementById('aiFilePreview');
        if (!previewDiv) return;

        if (this.selectedFiles.length === 0) {
            previewDiv.style.display = 'none';
            previewDiv.innerHTML = '';
            return;
        }

        previewDiv.style.display = 'flex';
        previewDiv.innerHTML = '';

        this.selectedFiles.forEach((file, index) => {
            const fileItem = document.createElement('div');
            fileItem.className = 'ai-file-item';
            
            // Create preview based on file type
            if (file.type.startsWith('image/')) {
                const reader = new FileReader();
                reader.onload = (e) => {
                    fileItem.innerHTML = `
                        <div class="ai-file-thumbnail">
                            <img src="${e.target.result}" alt="${file.name}">
                        </div>
                        <div class="ai-file-info">
                            <div class="ai-file-name">${this.truncateFileName(file.name)}</div>
                            <div class="ai-file-size">${this.formatFileSize(file.size)}</div>
                        </div>
                        <button class="ai-file-remove" data-index="${index}">×</button>
                    `;
                };
                reader.readAsDataURL(file);
            } else {
                // For non-image files, show icon
                const icon = this.getFileIcon(file.type);
                fileItem.innerHTML = `
                    <div class="ai-file-thumbnail ai-file-icon">
                        <i class="fas ${icon}"></i>
                    </div>
                    <div class="ai-file-info">
                        <div class="ai-file-name">${this.truncateFileName(file.name)}</div>
                        <div class="ai-file-size">${this.formatFileSize(file.size)}</div>
                    </div>
                    <button class="ai-file-remove" data-index="${index}">×</button>
                `;
            }
            
            previewDiv.appendChild(fileItem);
        });
    }

    getFileIcon(mimeType) {
        if (mimeType.includes('pdf')) return 'fa-file-pdf';
        if (mimeType.includes('word') || mimeType.includes('document')) return 'fa-file-word';
        if (mimeType.includes('text')) return 'fa-file-alt';
        return 'fa-file';
    }

    truncateFileName(name, maxLength = 20) {
        if (name.length <= maxLength) return name;
        const ext = name.split('.').pop();
        const nameWithoutExt = name.substring(0, name.lastIndexOf('.'));
        const truncated = nameWithoutExt.substring(0, maxLength - ext.length - 3);
        return `${truncated}...${ext}`;
    }

    formatFileSize(bytes) {
        if (bytes < 1024) return bytes + ' B';
        if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
        return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
    }

    updateLimitWarning() {
        const warningDiv = document.getElementById('aiLimitWarning');
        const warningText = document.getElementById('aiLimitText');
        
        if (this.isAuthenticated && this.isUnlimited) {
            warningDiv.style.display = 'none';
            return;
        }
        
        if (this.isAuthenticated && !this.isUnlimited && this.dailyLimit) {
            if (this.remainingQuestions !== null && this.remainingQuestions <= 5 && this.remainingQuestions > 0) {
                warningDiv.style.display = 'flex';
                warningDiv.classList.remove('error');
                warningText.textContent = `${this.remainingQuestions} pertanyaan tersisa hari ini.`;
            } else if (this.remainingQuestions === 0) {
                warningDiv.style.display = 'flex';
                warningDiv.classList.add('error');
                warningText.textContent = '🔒 Batas harian tercapai. Coba lagi besok!';
            } else {
                warningDiv.style.display = 'none';
            }
            return;
        }
        
        if (!this.isAuthenticated && this.remainingQuestions !== null) {
            if (this.remainingQuestions > 0) {
                warningDiv.style.display = 'flex';
                warningDiv.classList.remove('error');
                warningText.textContent = `Anda memiliki ${this.remainingQuestions} pesan gratis tersisa. Daftar atau login untuk lanjut chatting!`;
            } else {
                warningDiv.style.display = 'flex';
                warningDiv.classList.add('error');
                warningText.innerHTML = 'Kuota pesan gratis habis. <a href="/login" style="color: #721c24; font-weight: bold;">Login</a> atau <a href="/register" style="color: #721c24; font-weight: bold;">daftar</a> untuk lanjut chatting.';
            }
        } else {
            warningDiv.style.display = 'none';
        }
    }

    async sendMessage() {
        const input = document.getElementById('aiMessageInput');
        const message = input.value.trim();
        
        if (!message) return;

        if (!this.isAuthenticated && this.remainingQuestions <= 0) {
            this.showLoginPrompt();
            return;
        }

        // Clear input
        input.value = '';
        input.style.height = 'auto';

        // Add user message
        this.addMessage(message, 'user');

        // Show typing indicator
        this.showTyping();

        // Disable send button
        const sendBtn = document.getElementById('aiSendBtn');
        sendBtn.disabled = true;

        try {
            let response, data;

            const hasFiles = this.selectedFiles && this.selectedFiles.length > 0;

            if (hasFiles) {
                const form = new FormData();
                form.append('message', message);
                this.selectedFiles.forEach((file) => {
                    form.append('files[]', file, file.name);
                });

                response = await fetch('/ai-assistant/chat', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: form
                });
                data = await response.json();
            } else {
                response = await fetch('/ai-assistant/chat', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({ message })
                });

                data = await this.safeJson(response);
            }

            this.hideTyping();

            // Server balas tanpa JSON yang bisa dibaca (mis. halaman error
            // HTML). Tampilkan pesan ramah, jangan tampilkan error mentah.
            if (!data) {
                this.addMessage('Maaf, layanan Mersy sedang bermasalah. Silakan coba beberapa saat lagi ya.', 'ai');
                return;
            }

            if (data.success) {
                this.addMessage(data.answer, 'ai');

                // Clear files after successful send
                if (this.selectedFiles && this.selectedFiles.length > 0) {
                    this.selectedFiles = [];
                    const fi = document.getElementById('aiFileInput');
                    if (fi) fi.value = null;
                    this.updateFilePreview();
                }

                if (data.remaining_questions !== null && data.remaining_questions !== undefined) {
                    this.remainingQuestions = data.remaining_questions;
                    this.updateLimitWarning();
                }

                if (data.is_unlimited !== undefined) {
                    this.isUnlimited = data.is_unlimited;
                }

                if (data.daily_used !== undefined) {
                    this.dailyUsed = data.daily_used;
                }

                // Pesan terakhir tamu sudah terpakai: langsung tawarkan CTA.
                if (data.require_login) {
                    this.showCta(data.cta);
                }
            } else if (data.require_login) {
                this.addMessage(data.message || 'Kuota pesan gratis Anda sudah habis.', 'ai');
                this.remainingQuestions = 0;
                this.updateLimitWarning();
                this.showCta(data.cta);
            } else if (data.daily_limit_reached) {
                this.addMessage(data.message, 'ai');
                this.remainingQuestions = 0;
                this.updateLimitWarning();
                this.showCta(data.cta);
            } else {
                this.addMessage(data.message || 'Maaf, terjadi kesalahan. Silakan coba lagi.', 'ai');
            }
        } catch (error) {
            console.error('Error:', error);
            this.hideTyping();
            this.addMessage('Maaf, koneksi ke Mersy sedang terganggu. Coba kirim ulang pesannya sebentar lagi ya.', 'ai');
        } finally {
            sendBtn.disabled = false;
        }
    }

    addMessage(text, sender, timestamp = null) {
        const chatBody = document.getElementById('aiChatBody');
        const isUser = sender === 'user';
        
        const formattedText = isUser ? this.escapeHtml(text) : this.formatAiResponse(text);
        const time = timestamp ? this.formatTime(new Date(timestamp)) : this.formatTime(new Date());
        const avatarClass = isUser ? '' : 'robot';
        
        const messageHtml = `
            <div class="ai-message ${isUser ? 'user' : ''}">
                <div class="ai-message-avatar ${avatarClass}">${isUser ? '' : ''}</div>
                <div class="ai-message-content">
                    <div class="ai-message-bubble">${formattedText}</div>
                    <div class="ai-message-time">${time}</div>
                </div>
            </div>
        `;
        
        chatBody.insertAdjacentHTML('beforeend', messageHtml);
        this.scrollToBottom(true);
    }

    formatAiResponse(text) {
        let formatted = this.escapeHtml(text);
        
        const lines = formatted.split('\n');
        let result = [];
        let inList = false;
        let listItems = [];
        let currentHeading = null;
        
        for (let i = 0; i < lines.length; i++) {
            let line = lines[i].trim();
            
            if (!line) {
                if (inList && currentHeading) {
                    result.push(`<div class="ai-heading">${currentHeading}</div>`);
                    result.push(`<ul class="ai-list">${listItems.map(item => `<li>${item}</li>`).join('')}</ul>`);
                    listItems = [];
                    inList = false;
                    currentHeading = null;
                }
                continue;
            }
            
            if (line === '---' || line.match(/^-{3,}$/)) {
                if (inList && currentHeading) {
                    result.push(`<div class="ai-heading">${currentHeading}</div>`);
                    result.push(`<ul class="ai-list">${listItems.map(item => `<li>${item}</li>`).join('')}</ul>`);
                    listItems = [];
                    inList = false;
                    currentHeading = null;
                }
                result.push('<hr class="ai-separator">');
                continue;
            }
            
            const bulletMatch = line.match(/^[-*•]\s+(.+)$/);
            if (bulletMatch) {
                if (!inList) {
                    inList = true;
                }
                listItems.push(bulletMatch[1]);
                continue;
            }
            
            if (inList && currentHeading) {
                result.push(`<div class="ai-heading">${currentHeading}</div>`);
                result.push(`<ul class="ai-list">${listItems.map(item => `<li>${item}</li>`).join('')}</ul>`);
                listItems = [];
                inList = false;
                currentHeading = null;
            }
            
            // Check if this line ends with colon (heading detection)
            if (line.endsWith(':') && line.length < 100 && !line.match(/^[⚠️🔒📎]/)) {
                let nextLineIsBullet = false;
                for (let j = i + 1; j < lines.length; j++) {
                    const nextLine = lines[j].trim();
                    if (nextLine) {
                        if (nextLine.match(/^[-*•]\s+/)) {
                            nextLineIsBullet = true;
                        }
                        break;
                    }
                }
                
                if (nextLineIsBullet) {
                    // Heading with bullets following
                    currentHeading = line;
                    inList = true;
                } else {
                    // Heading without bullets (diikuti paragraf) - TETAP BIRU
                    result.push(`<div class="ai-heading">${line}</div>`);
                }
                continue;
            }
            
            if (line.match(/^[⚠️🔒📎]/)) {
                result.push(`<div class="ai-warning">${line}</div>`);
            } else {
                result.push(`<div class="ai-paragraph">${line}</div>`);
            }
        }
        
        if (inList && currentHeading) {
            result.push(`<div class="ai-heading">${currentHeading}</div>`);
            result.push(`<ul class="ai-list">${listItems.map(item => `<li>${item}</li>`).join('')}</ul>`);
        }
        
        return result.join('');
    }

    showTyping() {
        const chatBody = document.getElementById('aiChatBody');
        const typingHtml = `
            <div class="ai-message typing-indicator">
                <div class="ai-message-avatar robot"></div>
                <div class="ai-message-content">
                    <div class="ai-typing">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        `;
        chatBody.insertAdjacentHTML('beforeend', typingHtml);
        this.scrollToBottom(true);
    }

    hideTyping() {
        const typingIndicator = document.querySelector('.typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }

    showLoginPrompt() {
        const limit = this.dailyLimit || 3;
        this.addMessage(`Kuota ${limit} pesan gratis Anda sudah habis. Daftar atau login untuk lanjut mengobrol dengan saya — riwayat obrolan ini akan otomatis ikut pindah ke akun Anda. 🔐`, 'ai');
        this.showCta(this.pendingCta);
    }

    /**
     * Render tombol ajakan bertindak (CTA) di bawah percakapan.
     * Memakai class .ai-suggestion-btn yang sudah ada agar warna dan bentuk
     * tombol tetap konsisten dengan tampilan widget aslinya.
     */
    showCta(cta) {
        if (this.ctaShown) return;

        const actions = (cta && Array.isArray(cta.actions) && cta.actions.length)
            ? cta.actions
            : [
                { label: 'Login', url: '/login' },
                { label: 'Daftar Gratis', url: '/register' }
            ];

        const title = (cta && cta.title) || 'Daftar / Login untuk Lanjut Chatting';

        const buttons = actions.map(action => `
            <a class="ai-suggestion-btn ai-cta-btn" href="${this.escapeHtml(action.url || '#')}">
                ${this.escapeHtml(action.label || 'Lanjutkan')}
            </a>
        `).join('');

        const chatBody = document.getElementById('aiChatBody');
        chatBody.insertAdjacentHTML('beforeend', `
            <div class="ai-message">
                <div class="ai-message-avatar robot"></div>
                <div class="ai-message-content">
                    <div class="ai-cta">
                        <div class="ai-cta-title">🔐 ${this.escapeHtml(title)}</div>
                        <div class="ai-cta-actions">${buttons}</div>
                    </div>
                </div>
            </div>
        `);

        this.ctaShown = true;
        this.scrollToBottom(true);
    }

    async loadChatHistory() {
        try {
            const response = await fetch('/ai-assistant/history', {
                headers: { 'Accept': 'application/json' }
            });

            // Gagal memuat riwayat tidak boleh merusak widget: sapaan awal
            // tetap ditampilkan seperti biasa.
            if (!response.ok) {
                console.warn('AI Assistant: history responded', response.status);
                this.historyLoaded = true;
                return;
            }

            const data = await this.safeJson(response);

            if (!data) {
                this.historyLoaded = true;
                return;
            }

            if (data.success && Array.isArray(data.chats) && data.chats.length > 0) {
                const chatBody = document.getElementById('aiChatBody');
                chatBody.innerHTML = '';
                
                data.chats.forEach(chat => {
                    this.addMessageWithoutScroll(chat.question, 'user', chat.created_at);
                    this.addMessageWithoutScroll(chat.answer, 'ai', chat.created_at);
                });
                
                requestAnimationFrame(() => {
                    this.scrollToBottom(false);
                });
            }
            
            this.historyLoaded = true;
        } catch (error) {
            console.error('Error loading history:', error);
            this.historyLoaded = true;
        }
    }

    addMessageWithoutScroll(text, sender, timestamp = null) {
        const chatBody = document.getElementById('aiChatBody');
        const isUser = sender === 'user';
        
        const formattedText = isUser ? this.escapeHtml(text) : this.formatAiResponse(text);
        const time = timestamp ? this.formatTime(new Date(timestamp)) : this.formatTime(new Date());
        const avatarClass = isUser ? '' : 'robot';
        
        const messageHtml = `
            <div class="ai-message ${isUser ? 'user' : ''}">
                <div class="ai-message-avatar ${avatarClass}">${isUser ? '' : ''}</div>
                <div class="ai-message-content">
                    <div class="ai-message-bubble">${formattedText}</div>
                    <div class="ai-message-time">${time}</div>
                </div>
            </div>
        `;
        
        chatBody.insertAdjacentHTML('beforeend', messageHtml);
    }

    scrollToBottom(smooth = false) {
        const chatBody = document.getElementById('aiChatBody');
        
        if (smooth) {
            chatBody.scrollTo({
                top: chatBody.scrollHeight,
                behavior: 'smooth'
            });
        } else {
            chatBody.scrollTop = chatBody.scrollHeight;
        }
    }

    formatTime(date) {
        return date.toLocaleTimeString('id-ID', { 
            hour: '2-digit', 
            minute: '2-digit' 
        });
    }

    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
}

// Initialize when DOM is ready
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => {
        new AiAssistant();
    });
} else {
    new AiAssistant();
}