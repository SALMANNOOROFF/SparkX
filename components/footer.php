<footer class="dashboard-footer">
    <div class="footer-content">
        <p>&copy; 2026 Sparkx. All rights reserved.</p>
    </div>
</footer>

<!-- Mobile Bottom Navigation -->
<nav class="mobile-bottom-nav" id="mobileBottomNav">
    <a href="<?php echo SITE_URL; ?>/user/dashboard" class="mobile-nav-item">
        <div class="mobile-nav-icon"><i class="fas fa-tachometer-alt"></i></div>
        <span class="mobile-nav-label">Dashboard</span>
    </a>
    <a href="<?php echo SITE_URL; ?>/user/dashboard/deposit" class="mobile-nav-item">
        <div class="mobile-nav-icon"><i class="fas fa-plus-circle"></i></div>
        <span class="mobile-nav-label">Recharge</span>
    </a>
    <a href="<?php echo SITE_URL; ?>/user/dashboard/referrals.php" class="mobile-nav-item">
        <div class="mobile-nav-icon"><i class="fas fa-share-alt"></i></div>
        <span class="mobile-nav-label">Referrals</span>
    </a>
    <a href="<?php echo SITE_URL; ?>/user/dashboard/plans" class="mobile-nav-item">
        <div class="mobile-nav-icon"><i class="fas fa-layer-group"></i></div>
        <span class="mobile-nav-label">Plans</span>
    </a>
    <a href="<?php echo SITE_URL; ?>/user/dashboard/support" class="mobile-nav-item">
        <div class="mobile-nav-icon"><i class="fas fa-headset"></i></div>
        <span class="mobile-nav-label">Support</span>
    </a>
</nav>

<!-- Chat Button -->
<div class="chat-button-container">
    <button class="chat-button" id="chatButton" aria-label="Open chat">
        <i class="fas fa-comments"></i>
        <span class="chat-notification-badge" id="chatBadge"></span>
    </button>
</div>

<!-- Start Chat Modal -->
<div class="chat-modal-overlay" id="startChatModal" style="display: none;">
    <div class="chat-modal">
        <div class="chat-modal-header">
            <h3>Start a Chat</h3>
            <button class="chat-modal-close" id="closeStartChatModal" aria-label="Close">
                <i class="fas fa-times"></i>
            </button>
        </div>
        <div class="chat-modal-body">
            <form id="startChatForm">
                <input type="hidden" name="_token" value="8c7BwEZOfQtpnaM8OQ0i2p8H4BUwPgx3HbDVFjyO" autocomplete="off">
                <div class="form-group">
                    <label for="chatName">Name *</label>
                    <input type="text" id="chatName" name="name" class="form-control" placeholder="Enter your name" required>
                </div>
                <div class="form-group">
                    <label for="chatEmail">Email *</label>
                    <input type="email" id="chatEmail" name="email" class="form-control" placeholder="Enter your email" required>
                </div>
                <div class="form-group">
                    <label for="chatMessage">Message *</label>
                    <textarea id="chatMessage" name="message" class="form-control" rows="4" placeholder="Type your message" required></textarea>
                </div>
                <button type="submit" class="btn btn-primary btn-block" id="startChatBtn">
                    Start Chat
                </button>
            </form>
        </div>
    </div>
</div>

<!-- Live Chat Window -->
<div class="live-chat-window" id="liveChatWindow" style="display: none;">
    <div class="live-chat-header">
        <div class="live-chat-header-left">
            <h4>Live Chat</h4>
            <span class="live-chat-status" id="chatStatus">Waiting for agent...</span>
        </div>
        <button class="live-chat-close" id="closeLiveChat" aria-label="Close chat">
            <i class="fas fa-times"></i>
        </button>
    </div>
    <div class="live-chat-messages" id="chatMessages"></div>
    <div class="live-chat-input-container">
        <div class="live-chat-input-actions">
            <button class="chat-action-btn" id="refreshChatBtn" aria-label="Refresh">
                <i class="fas fa-redo"></i>
            </button>
            <button class="chat-action-btn" id="imageChatBtn" aria-label="Upload image" type="button">
                <i class="fas fa-image"></i>
            </button>
            <input type="file" id="chatImageInput" accept="image/*" style="display: none;">
        </div>
        <input type="text" id="chatMessageInput" class="live-chat-input" placeholder="Ask me anything...">
        <button type="button" class="live-chat-send" id="sendChatMessage" aria-label="Send message">
            <i class="fas fa-arrow-up"></i>
        </button>
    </div>
</div>
