<?php 
    require_once '../../includes/auth_check.php';
    $title = "Sparkx - Support";
    $base_url = "../..";

    include('../../components/layout_top.php'); 
?>

                <div class="support-new-page">
                    <!-- Hero Section -->
                    <div class="support-hero-new">
                        <div class="support-hero-content-new">
                            <h1 class="support-hero-title-new">Support</h1>
                            <p class="support-hero-subtitle-new">Get in touch with our support team through multiple channels. We're here to help you 24/7</p>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="support-quick-actions-new">
                        <div class="support-quick-action-new" id="support247Action" style="cursor: pointer;" data-phone="<?php echo htmlspecialchars(get_setting('support_phone', '+92 323 9704664')); ?>">
                            <div class="support-quick-action-icon-new">
                                <i class="fas fa-headset"></i>
                            </div>
                            <div class="support-quick-action-content-new">
                                <h3 class="support-quick-action-title-new">24/7 Support</h3>
                                <p class="support-quick-action-desc-new"><?php echo htmlspecialchars(get_setting('support_phone', '+92 323 9704664')); ?><br></p>
                            </div>
                        </div>
                        <div class="support-quick-action-new" id="liveChatAction" style="cursor: pointer;">
                            <div class="support-quick-action-icon-new">
                                <i class="fas fa-comments"></i>
                            </div>
                            <div class="support-quick-action-content-new">
                                <h3 class="support-quick-action-title-new">Live Chat</h3>
                                <p class="support-quick-action-desc-new">Instant responses via WhatsApp</p>
                            </div>
                        </div>
                    </div>

                    <!-- Support Channels Section -->
                    <div class="support-channels-section-new">
                        <div class="support-channels-header-new">
                            <h2 class="support-channels-title-new">Contact Channels</h2>
                            <p class="support-channels-subtitle-new">Choose your preferred method to reach our support team</p>
                        </div>

                        <div class="support-channels-grid-new">
                            <!-- WhatsApp Card -->
                            <div class="support-channel-card-new">
                                <div class="support-channel-header-new">
                                    <div class="support-channel-icon-wrapper-new support-channel-icon-whatsapp-new">
                                        <i class="fab fa-whatsapp"></i>
                                    </div>
                                    <h3 class="support-channel-name-new">WhatsApp</h3>
                                </div>
                                <div class="support-channel-items-new">
                                    <div class="support-channel-item-new">
                                        <div class="support-item-header-new">
                                            <div class="support-item-label-new">WhatsApp Channel</div>
                                        </div>
                                        <div class="support-item-value-new" id="whatsappChannel"><?php echo htmlspecialchars(get_setting('whatsapp_channel_url', 'https://whatsapp.com/channel/0029Vb8Wl8Q1t90U7qiFnB0j')); ?></div>
                                        <div class="support-item-actions-new">
                                            <button class="support-copy-btn-new" data-copy="whatsappChannel" title="Copy">
                                                <i class="fas fa-copy"></i>
                                                <span>Copy</span>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="support-channel-item-new">
                                        <div class="support-item-header-new">
                                            <div class="support-item-label-new">WhatsApp Customer Service</div>
                                        </div>
                                        <div class="support-item-value-new" id="whatsappCustomerService"><?php echo htmlspecialchars(get_setting('whatsapp_support_link', '+92 323 9704664')); ?></div>
                                        <div class="support-item-actions-new">
                                            <button class="support-copy-btn-new" data-copy="whatsappCustomerService" title="Copy">
                                                <i class="fas fa-copy"></i>
                                                <span>Copy</span>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Contact Info Section -->
                    <div class="support-contact-section-new">
                        <div class="support-contact-header-new">
                            <h2 class="support-contact-title-new">All Contact Information</h2>
                            <p class="support-contact-subtitle-new">Quick access to all support contact details</p>
                        </div>
                        <div class="support-contact-grid-new">
                            <div class="support-contact-card-new">
                                <div class="support-contact-icon-new">
                                    <i class="fab fa-whatsapp"></i>
                                </div>
                                <div class="support-contact-label-new">WhatsApp Channel</div>
                                <div class="support-contact-value-new"><?php echo htmlspecialchars(get_setting('whatsapp_channel_url', 'https://whatsapp.com/channel/0029VbC0KoL5kg73qAwejl3L')); ?></div>
                            </div>
                            <div class="support-contact-card-new">
                                <div class="support-contact-icon-new">
                                    <i class="fas fa-phone"></i>
                                </div>
                                <div class="support-contact-label-new">WhatsApp Number</div>
                                <div class="support-contact-value-new"><?php echo htmlspecialchars(get_setting('whatsapp_number', '+16474986701')); ?></div>
                            </div>
                            <div class="support-contact-card-new">
                                <div class="support-contact-icon-new">
                                    <i class="fas fa-headset"></i>
                                </div>
                                <div class="support-contact-label-new">Support Phone</div>
                                <div class="support-contact-value-new"><?php echo htmlspecialchars(get_setting('support_phone', '+16474986701')); ?></div>
                            </div>
                            <div class="support-contact-card-new">
                                <div class="support-contact-icon-new">
                                    <i class="fab fa-facebook-f"></i>
                                </div>
                                <div class="support-contact-label-new">Facebook Page</div>
                                <div class="support-contact-value-new"><?php echo htmlspecialchars(get_setting('facebook_page_url', 'https://www.facebook.com/Licrownpvt/')); ?></div>
                            </div>
                            <div class="support-contact-card-new">
                                <div class="support-contact-icon-new">
                                    <i class="fab fa-facebook-messenger"></i>
                                </div>
                                <div class="support-contact-label-new">Facebook Contact</div>
                                <div class="support-contact-value-new"><?php echo htmlspecialchars(get_setting('facebook_contact_url', 'licrownltd')); ?></div>
                            </div>
                        </div>
                    </div>
                </div> <!-- support-new-page -->

<script>
document.addEventListener('DOMContentLoaded', function() {
    const supportAction = document.getElementById('support247Action');
    if (supportAction) {
        // Clone and replace to remove original hardcoded listener from main-bundle.js
        const newSupportAction = supportAction.cloneNode(true);
        supportAction.parentNode.replaceChild(newSupportAction, supportAction);
        
        newSupportAction.addEventListener('click', function() {
            const phoneNumber = this.getAttribute('data-phone');
            if (navigator.clipboard && navigator.clipboard.writeText) {
                navigator.clipboard.writeText(phoneNumber).then(() => {
                    if (window.showInfoMessage) {
                        window.showInfoMessage('Support Phone: ' + phoneNumber + '\n(Copied to clipboard)');
                    } else {
                        alert('Support Phone: ' + phoneNumber + '\n(Copied to clipboard)');
                    }
                }).catch(() => {
                    alert('Support Phone: ' + phoneNumber);
                });
            } else {
                alert('Support Phone: ' + phoneNumber);
            }
        });
    }
});
</script>

<?php include('../../components/layout_bottom.php'); ?>
