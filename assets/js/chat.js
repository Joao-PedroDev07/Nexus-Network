// Sistema de Chat - Nexus Network
		class ChatSystem {
			constructor() {
				this.currentUser = null;
				this.currentChat = null;
				this.contacts = [];
				this.messages = {};
				this.unreadCounts = {};
				this.isTyping = {};
				
				// Simular dados do usuário logado (integrar com seu sistema de login)
				this.currentUser = {
					id: 2,
					name: 'João Pedro Ribas',
					type: 'cliente', // ou 'prestador'
					avatar: 'assets/img/patients/patient-thumb-01.jpg'
				};
				
				this.init();
			}
			
			init() {
				this.loadContacts();
				this.setupEventListeners();
				this.startMessagePolling();
			}
			
			setupEventListeners() {
				// Pesquisa de contatos
				document.getElementById('searchContacts').addEventListener('input', (e) => {
					this.filterContacts(e.target.value);
				});
				
				// Envio de mensagem
				document.getElementById('sendBtn').addEventListener('click', () => {
					this.sendMessage();
				});
				
				document.getElementById('messageInput').addEventListener('keypress', (e) => {
					if (e.key === 'Enter' && !e.shiftKey) {
						e.preventDefault();
						this.sendMessage();
					}
					this.handleTyping();
				});
				
				// Upload de arquivos
				document.getElementById('fileInput').addEventListener('change', (e) => {
					this.handleFileUpload(e.target.files);
				});
				
				// Prevenir comportamento padrão do drag and drop
				['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
					document.addEventListener(eventName, (e) => {
						e.preventDefault();
						e.stopPropagation();
					});
				});
				
				// Drag and drop de arquivos
				const chatBody = document.getElementById('chatBody');
				chatBody.addEventListener('drop', (e) => {
					const files = e.dataTransfer.files;
					this.handleFileUpload(files);
				});
			}
			
			async loadContacts() {
				try {
					const response = await fetch('api/contacts.php');
					
					if (!response.ok) {
						throw new Error(`HTTP error! status: ${response.status}`);
					}
					
					this.contacts = await response.json();
					
					// Inicializar contadores de mensagens não lidas
					this.contacts.forEach(contact => {
						this.unreadCounts[contact.id] = 0;
						this.messages[contact.id] = [];
					});
					
					this.renderContacts();
				} catch (error) {
					console.error('Erro ao carregar contatos:', error);
					// Fallback para dados simulados em caso de erro
					this.contacts = [
						{
							id: 1,
							name: 'Juan Silva',
							type: 'prestador',
							avatar: 'assets/img/doctors/doctor-thumb-01.jpg',
							profession: 'Mecânico',
							lastMessage: 'Olá! Como posso ajudar?',
							lastMessageTime: '2 min',
							online: true
						},
						{
							id: 2,
							name: 'Maria Eduarda Gomes',
							type: 'prestador',
							avatar: 'assets/img/doctors/doctor-thumb-02.jpg',
							profession: 'Manicure',
							lastMessage: 'Obrigada pelo contato!',
							lastMessageTime: '1 hora',
							online: false
						}
					];
					
					this.contacts.forEach(contact => {
						this.unreadCounts[contact.id] = 0;
						this.messages[contact.id] = this.generateSampleMessages(contact.id);
					});
					
					this.renderContacts();
				}
			}
			
			generateSampleMessages(contactId) {
				const sampleMessages = [
					{
						id: 1,
						sender: contactId,
						content: 'Olá! Como está?',
						timestamp: new Date(Date.now() - 3600000),
						type: 'text'
					},
					{
						id: 2,
						sender: this.currentUser.id,
						content: 'Oi! Tudo bem, obrigado. E você?',
						timestamp: new Date(Date.now() - 3540000),
						type: 'text'
					},
					{
						id: 3,
						sender: contactId,
						content: 'Também estou bem! Posso ajudar em algo?',
						timestamp: new Date(Date.now() - 3480000),
						type: 'text'
					}
				];
				
				return sampleMessages;
			}
			
			renderContacts() {
				const contactsList = document.getElementById('contactsList');
				contactsList.innerHTML = '';
				
				this.contacts.forEach(contact => {
					const unreadCount = this.unreadCounts[contact.id] || 0;
					const contactElement = document.createElement('div');
					contactElement.className = 'contact-item';
					contactElement.dataset.contactId = contact.id;
					
					contactElement.innerHTML = `
						<img src="${contact.avatar}" alt="${contact.name}" class="contact-avatar">
						<div class="contact-info flex-grow-1">
							<h6>${contact.name}</h6>
							<small>${contact.profession}</small>
						</div>
						<div class="contact-status">
							${contact.online ? '<i class="fas fa-circle text-success" style="font-size: 8px;"></i>' : ''}
							${unreadCount > 0 ? `<span class="unread-count">${unreadCount}</span>` : ''}
						</div>
					`;
					
					contactElement.addEventListener('click', () => {
						this.selectContact(contact);
					});
					
					contactsList.appendChild(contactElement);
				});
			}
			
			filterContacts(searchTerm) {
				const contactItems = document.querySelectorAll('.contact-item');
				contactItems.forEach(item => {
					const contactName = item.querySelector('h6').textContent.toLowerCase();
					const contactProfession = item.querySelector('small').textContent.toLowerCase();
					const matches = contactName.includes(searchTerm.toLowerCase()) || 
									contactProfession.includes(searchTerm.toLowerCase());
					item.style.display = matches ? 'flex' : 'none';
				});
			}
			
			selectContact(contact) {
				// Remover seleção anterior
				document.querySelectorAll('.contact-item').forEach(item => {
					item.classList.remove('active');
				});
				
				// Adicionar seleção atual
				const contactElement = document.querySelector(`[data-contact-id="${contact.id}"]`);
				contactElement.classList.add('active');
				
				// Limpar contador de não lidas
				this.unreadCounts[contact.id] = 0;
				this.renderContacts();
				
				this.currentChat = contact;
				this.renderChatHeader(contact);
				this.loadMessages(contact.id);
				this.enableChatInput();
				
				// Esconder lista de contatos no mobile
				if (window.innerWidth <= 768) {
					document.getElementById('chatLeft').classList.remove('mobile-show');
					document.getElementById('chatRight').classList.remove('mobile-hide');
				}
			}
			
			renderChatHeader(contact) {
				const chatHeader = document.getElementById('chatHeader');
				chatHeader.innerHTML = `
					<button class="btn btn-sm btn-outline-primary d-md-none mr-3" onclick="toggleMobileChat()">
						<i class="fas fa-arrow-left"></i>
					</button>
					<div class="d-flex align-items-center">
						<img src="${contact.avatar}" alt="${contact.name}" class="contact-avatar mr-3">
						<div>
							<h6 class="mb-0">${contact.name}</h6>
							<small class="text-muted">${contact.profession} • ${contact.online ? 'Online' : 'Offline'}</small>
						</div>
					</div>
				`;
			}
			
			async loadMessages(contactId) {
				try {
					const response = await fetch(`api/messages.php?contact_id=${contactId}`);
					
					if (!response.ok) {
						throw new Error(`HTTP error! status: ${response.status}`);
					}
					
					const messages = await response.json();
					this.messages[contactId] = messages.map(msg => ({
						id: msg.msg_id,
						sender: msg.remetente_tipo === 'cliente' ? 
							msg.remetente_id : msg.remetente_id,
						content: msg.conteudo,
						timestamp: new Date(msg.data_envio),
						type: msg.tipo_mensagem === 'arquivo' ? 'file' : 'text',
						fileName: msg.arquivo_nome,
						fileUrl: msg.arquivo_url,
						fileSize: msg.arquivo_tamanho
					}));
					
				} catch (error) {
					console.error('Erro ao carregar mensagens:', error);
					// Fallback para mensagens simuladas
					if (!this.messages[contactId]) {
						this.messages[contactId] = this.generateSampleMessages(contactId);
					}
				}
				
				const messagesList = document.getElementById('messagesList');
				messagesList.innerHTML = '';
				
				const messages = this.messages[contactId] || [];
				messages.forEach(message => {
					this.renderMessage(message);
				});
				
				this.scrollToBottom();
			}
			
			renderMessage(message) {
				const messagesList = document.getElementById('messagesList');
				const messageElement = document.createElement('div');
				const isOwn = message.sender === this.currentUser.id;
				
				messageElement.className = `message ${isOwn ? 'sent' : 'received'}`;
				
				let messageContent = '';
				if (message.type === 'text') {
					messageContent = `<p class="mb-1">${this.escapeHtml(message.content)}</p>`;
				} else if (message.type === 'file') {
					const fileIcon = this.getFileIcon(message.fileName);
					messageContent = `
						<div class="file-attachment">
							<i class="${fileIcon} file-icon"></i>
							<div class="file-info">
								<div class="file-name">${message.fileName}</div>
								<div class="file-size">${this.formatFileSize(message.fileSize)}</div>
							</div>
							<a href="${message.fileUrl}" download class="btn btn-sm btn-outline-primary ml-2">
								<i class="fas fa-download"></i>
							</a>
						</div>
					`;
				}
				
				messageElement.innerHTML = `
					<div class="message-content">
						${messageContent}
						<div class="message-time">${this.formatTime(message.timestamp)}</div>
					</div>
				`;
				
				messagesList.appendChild(messageElement);
			}
			
			async sendMessage() {
				const input = document.getElementById('messageInput');
				const content = input.value.trim();
				
				if (!content || !this.currentChat) return;
				
				try {
					// Mostrar mensagem imediatamente na interface
					const tempMessage = {
						id: 'temp_' + Date.now(),
						sender: this.currentUser.id,
						content: content,
						timestamp: new Date(),
						type: 'text',
						sending: true
					};
					
					// Adicionar mensagem temporária
					if (!this.messages[this.currentChat.id]) {
						this.messages[this.currentChat.id] = [];
					}
					this.messages[this.currentChat.id].push(tempMessage);
					this.renderMessage(tempMessage);
					this.scrollToBottom();
					
					// Limpar input imediatamente
					input.value = '';
					
					// Enviar para o servidor
					const response = await fetch('api/send_message.php', {
						method: 'POST',
						headers: {
							'Content-Type': 'application/json',
						},
						body: JSON.stringify({
							contact_id: this.currentChat.id,
							message: content,
							type: 'texto'
						})
					});
					
					if (!response.ok) {
						throw new Error(`HTTP error! status: ${response.status}`);
					}
					
					const result = await response.json();
					
					// Remover mensagem temporária e adicionar a real
					this.messages[this.currentChat.id] = this.messages[this.currentChat.id]
						.filter(msg => msg.id !== tempMessage.id);
					
					const realMessage = {
						id: result.id,
						sender: this.currentUser.id,
						content: result.content,
						timestamp: new Date(result.timestamp),
						type: result.type
					};
					
					this.messages[this.currentChat.id].push(realMessage);
					
					// Recarregar mensagens para mostrar a real
					this.loadMessages(this.currentChat.id);
					
				} catch (error) {
					console.error('Erro ao enviar mensagem:', error);
					
					// Remover mensagem temporária em caso de erro
					if (this.messages[this.currentChat.id]) {
						this.messages[this.currentChat.id] = this.messages[this.currentChat.id]
							.filter(msg => !msg.sending);
						this.loadMessages(this.currentChat.id);
					}
					
					// Mostrar erro para o usuário
					alert('Erro ao enviar mensagem. Tente novamente.');
					input.value = content; // Restaurar o texto
				}
			}
			
			simulateResponse() {
				if (!this.currentChat) return;
				
				const responses = [
					'Entendi!',
					'Perfeito, obrigado!',
					'Vou verificar isso para você.',
					'Pode contar comigo!',
					'Ótimo, vamos conversar mais sobre isso.',
					'Combinado!'
				];
				
				const response = {
					id: Date.now(),
					sender: this.currentChat.id,
					content: responses[Math.floor(Math.random() * responses.length)],
					timestamp: new Date(),
					type: 'text'
				};
				
				this.messages[this.currentChat.id].push(response);
				this.renderMessage(response);
				this.scrollToBottom();
			}
			
			async handleFileUpload(files) {
				if (!this.currentChat || !files.length) return;
				
				for (const file of Array.from(files)) {
					// Validar tamanho do arquivo (máximo 10MB)
					if (file.size > 10 * 1024 * 1024) {
						alert(`Arquivo ${file.name} é muito grande. Máximo permitido: 10MB`);
						continue;
					}
					
					try {
						// Mostrar indicador de upload
						const uploadIndicator = this.showUploadIndicator(file.name);
						
						// Preparar FormData para upload
						const formData = new FormData();
						formData.append('file', file);
						formData.append('contact_id', this.currentChat.id);
						
						// Fazer upload do arquivo
						const uploadResponse = await fetch('api/upload.php', {
							method: 'POST',
							body: formData
						});
						
						if (!uploadResponse.ok) {
							throw new Error(`Erro no upload: ${uploadResponse.status}`);
						}
						
						const uploadResult = await uploadResponse.json();
						
						// Remover indicador de upload
						this.removeUploadIndicator(uploadIndicator);
						
						// Enviar mensagem com arquivo
						const messageResponse = await fetch('api/send_message.php', {
							method: 'POST',
							headers: {
								'Content-Type': 'application/json',
							},
							body: JSON.stringify({
								contact_id: this.currentChat.id,
								message: `Arquivo: ${file.name}`,
								type: 'arquivo',
								file_name: uploadResult.filename,
								file_url: uploadResult.url,
								file_size: uploadResult.size
							})
						});
						
						if (!messageResponse.ok) {
							throw new Error(`Erro ao enviar mensagem: ${messageResponse.status}`);
						}
						
						const messageResult = await messageResponse.json();
						
						// Adicionar mensagem com arquivo
						const fileMessage = {
							id: messageResult.id,
							sender: this.currentUser.id,
							fileName: uploadResult.filename,
							fileSize: uploadResult.size,
							fileUrl: uploadResult.url,
							timestamp: new Date(messageResult.timestamp),
							type: 'file'
						};
						
						this.messages[this.currentChat.id].push(fileMessage);
						this.renderMessage(fileMessage);
						this.scrollToBottom();
						
					} catch (error) {
						console.error('Erro no upload:', error);
						alert(`Erro ao enviar arquivo ${file.name}: ${error.message}`);
					}
				}
				
				// Limpar input de arquivo
				document.getElementById('fileInput').value = '';
			}
			
			showUploadIndicator(fileName) {
				const messagesList = document.getElementById('messagesList');
				const indicator = document.createElement('div');
				indicator.className = 'message sent';
				indicator.innerHTML = `
					<div class="message-content">
						<div class="d-flex align-items-center">
							<div class="spinner-border spinner-border-sm mr-2" role="status">
								<span class="sr-only">Enviando...</span>
							</div>
							<small>Enviando ${fileName}...</small>
						</div>
					</div>
				`;
				messagesList.appendChild(indicator);
				this.scrollToBottom();
				return indicator;
			}
			
			removeUploadIndicator(indicator) {
				if (indicator && indicator.parentNode) {
					indicator.parentNode.removeChild(indicator);
				}
			}
			
			handleTyping() {
				// Implementar indicador de digitação (integrar com WebSocket)
				if (!this.currentChat) return;
				
				// Simular indicador de digitação
				clearTimeout(this.typingTimeout);
				this.typingTimeout = setTimeout(() => {
					// Parar indicador de digitação
				}, 1000);
			}
			
			enableChatInput() {
				const messageInput = document.getElementById('messageInput');
				const sendBtn = document.getElementById('sendBtn');
				const chatFooter = document.getElementById('chatFooter');
				
				messageInput.disabled = false;
				sendBtn.disabled = false;
				chatFooter.style.display = 'block';
			}
			
			scrollToBottom() {
				const messagesList = document.getElementById('messagesList');
				messagesList.scrollTop = messagesList.scrollHeight;
			}
			
			formatTime(timestamp) {
				const now = new Date();
				const diff = now - timestamp;
				const minutes = Math.floor(diff / (1000 * 60));
				const hours = Math.floor(diff / (1000 * 60 * 60));
				const days = Math.floor(diff / (1000 * 60 * 60 * 24));
				
				if (minutes < 1) return 'Agora';
				if (minutes < 60) return `${minutes} min`;
				if (hours < 24) return `${hours}h`;
				if (days < 7) return `${days}d`;
				
				return timestamp.toLocaleDateString('pt-BR', {
					day: '2-digit',
					month: '2-digit',
					hour: '2-digit',
					minute: '2-digit'
				});
			}
			
			formatFileSize(bytes) {
				if (bytes === 0) return '0 Bytes';
				const k = 1024;
				const sizes = ['Bytes', 'KB', 'MB', 'GB'];
				const i = Math.floor(Math.log(bytes) / Math.log(k));
				return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
			}
			
			getFileIcon(fileName) {
				const extension = fileName.split('.').pop().toLowerCase();
				const iconMap = {
					pdf: 'fas fa-file-pdf',
					doc: 'fas fa-file-word',
					docx: 'fas fa-file-word',
					xls: 'fas fa-file-excel',
					xlsx: 'fas fa-file-excel',
					ppt: 'fas fa-file-powerpoint',
					pptx: 'fas fa-file-powerpoint',
					jpg: 'fas fa-file-image',
					jpeg: 'fas fa-file-image',
					png: 'fas fa-file-image',
					gif: 'fas fa-file-image',
					mp4: 'fas fa-file-video',
					avi: 'fas fa-file-video',
					mp3: 'fas fa-file-audio',
					wav: 'fas fa-file-audio',
					zip: 'fas fa-file-archive',
					rar: 'fas fa-file-archive',
					txt: 'fas fa-file-alt'
				};
				
				return iconMap[extension] || 'fas fa-file';
			}
			
			escapeHtml(text) {
				const div = document.createElement('div');
				div.textContent = text;
				return div.innerHTML;
			}
			
			startMessagePolling() {
				// Polling para verificar novas mensagens
				setInterval(async () => {
					if (!this.currentChat) return;
					
					try {
						// Verificar novas mensagens para o chat atual
						const response = await fetch(`api/check_new_messages.php?contact_id=${this.currentChat.id}&last_message_id=${this.getLastMessageId()}`);
						
						if (response.ok) {
							const newMessages = await response.json();
							
							if (newMessages.length > 0) {
								newMessages.forEach(msg => {
									const message = {
										id: msg.msg_id,
										sender: msg.remetente_tipo === 'cliente' ? msg.remetente_id : msg.remetente_id,
										content: msg.conteudo,
										timestamp: new Date(msg.data_envio),
										type: msg.tipo_mensagem === 'arquivo' ? 'file' : 'text',
										fileName: msg.arquivo_nome,
										fileUrl: msg.arquivo_url,
										fileSize: msg.arquivo_tamanho
									};
									
									this.messages[this.currentChat.id].push(message);
									this.renderMessage(message);
								});
								
								this.scrollToBottom();
								this.playNotificationSound();
							}
						}
						
						// Verificar contadores de mensagens não lidas
						const contactsResponse = await fetch('api/unread_counts.php');
						if (contactsResponse.ok) {
							const unreadData = await contactsResponse.json();
							let hasUpdates = false;
							
							Object.keys(unreadData).forEach(contactId => {
								if (this.unreadCounts[contactId] !== unreadData[contactId]) {
									this.unreadCounts[contactId] = unreadData[contactId];
									hasUpdates = true;
								}
							});
							
							if (hasUpdates) {
								this.renderContacts();
							}
						}
						
					} catch (error) {
						console.error('Erro no polling de mensagens:', error);
					}
				}, 3000); // Verificar a cada 3 segundos
				
				// Fallback: simulação ocasional (remover quando APIs estiverem funcionando)
				setInterval(() => {
					if (Math.random() < 0.05 && this.contacts.length > 0) { // 5% de chance
						const randomContact = this.contacts[Math.floor(Math.random() * this.contacts.length)];
						
						if (this.currentChat && this.currentChat.id === randomContact.id) return;
						
						this.unreadCounts[randomContact.id] = (this.unreadCounts[randomContact.id] || 0) + 1;
						this.renderContacts();
						this.playNotificationSound();
					}
				}, 10000); // A cada 10 segundos
			}
			
			getLastMessageId() {
				if (!this.currentChat || !this.messages[this.currentChat.id]) return 0;
				
				const messages = this.messages[this.currentChat.id];
				if (messages.length === 0) return 0;
				
				return Math.max(...messages.map(msg => parseInt(msg.id) || 0));
			}
			
			playNotificationSound() {
				// Criar um som de notificação simples
				const audioContext = new (window.AudioContext || window.webkitAudioContext)();
				const oscillator = audioContext.createOscillator();
				const gainNode = audioContext.createGain();
				
				oscillator.connect(gainNode);
				gainNode.connect(audioContext.destination);
				
				oscillator.frequency.value = 800;
				oscillator.type = 'sine';
				
				gainNode.gain.setValueAtTime(0.3, audioContext.currentTime);
				gainNode.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
				
				oscillator.start(audioContext.currentTime);
				oscillator.stop(audioContext.currentTime + 0.5);
			}
		}
		
		// Funções auxiliares globais
		function toggleMobileChat() {
			const chatLeft = document.getElementById('chatLeft');
			const chatRight = document.getElementById('chatRight');
			
			if (chatLeft.classList.contains('mobile-show')) {
				chatLeft.classList.remove('mobile-show');
				chatRight.classList.remove('mobile-hide');
			} else {
				chatLeft.classList.add('mobile-show');
				chatRight.classList.add('mobile-hide');
			}
		}
		
		// Inicializar o sistema de chat quando a página carregar
		document.addEventListener('DOMContentLoaded', function() {
			window.chatSystem = new ChatSystem();
			
			// Adicionar suporte a PWA (opcional)
			if ('serviceWorker' in navigator) {
				navigator.serviceWorker.register('/sw.js').catch(console.error);
			}
			
			// Adicionar suporte a notificações (opcional)
			if ('Notification' in window && Notification.permission === 'default') {
				Notification.requestPermission();
			}
		});
		
		// Eventos de redimensionamento
		window.addEventListener('resize', function() {
			if (window.innerWidth > 768) {
				document.getElementById('chatLeft').classList.remove('mobile-show');
				document.getElementById('chatRight').classList.remove('mobile-hide');
			}
		});
		
		// Eventos de visibilidade da página
		document.addEventListener('visibilitychange', function() {
			if (document.hidden) {
				// Página não está visível, reduzir polling
			} else {
				// Página está visível, recarregar mensagens se necessário
				if (window.chatSystem && window.chatSystem.currentChat) {
					window.chatSystem.loadMessages(window.chatSystem.currentChat.id);
				}
			}
		});