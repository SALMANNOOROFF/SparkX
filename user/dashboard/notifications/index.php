<?php
// user/dashboard/notifications/index.php
require_once '../../../includes/auth_check.php';

// Check if this is an AJAX/JSON request
$is_ajax = isset($_GET['ajax']) || (isset($_SERVER['HTTP_X_REQUESTED_WITH']) && $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest');

if ($is_ajax) {
    header('Content-Type: application/json');
    $query = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id = '$user_id' ORDER BY created_at DESC LIMIT 15");
    $notifications = [];
    while ($row = mysqli_fetch_assoc($query)) {
        // Format time nicely
        $row['created_at'] = date('M d, H:i', strtotime($row['created_at']));
        $notifications[] = $row;
    }
    echo json_encode([
        'success' => true,
        'notifications' => $notifications
    ]);
    exit();
}

// Otherwise, render full browser HTML
$title = "Sparkx - Notifications";
$base_url = "../../..";
include('../../../components/layout_top.php');
?>

<style>
.notifications-wrapper {
    padding: 2.5rem 1.5rem;
    min-height: 80vh;
    font-family: 'Poppins', sans-serif;
    max-width: 850px;
    margin: 0 auto;
}

.notifications-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 2rem;
    border-bottom: 1px solid #e5e7eb;
    padding-bottom: 1.25rem;
}

.notifications-title {
    font-size: 1.8rem;
    font-weight: 700;
    margin: 0;
    background: linear-gradient(135deg, #f97316 0%, #fdba74 100%);
    -webkit-background-clip: text;
    -webkit-text-fill-color: transparent;
    background-clip: text;
}

.btn-mark-all {
    background: #ea580c;
    color: #ffffff;
    border: none;
    padding: 10px 22px;
    border-radius: 30px;
    font-size: 0.875rem;
    font-weight: 600;
    cursor: pointer;
    box-shadow: 0 4px 12px rgba(234, 88, 12, 0.2);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.btn-mark-all:hover {
    background: #c2410c;
    transform: translateY(-1.5px);
    box-shadow: 0 6px 16px rgba(234, 88, 12, 0.35);
}

.btn-mark-all:active {
    transform: translateY(0);
}

.notifications-list {
    display: flex;
    flex-direction: column;
    gap: 1.25rem;
}

.notification-card {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 16px;
    padding: 1.25rem 1.5rem;
    display: flex;
    gap: 1.25rem;
    align-items: flex-start;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    position: relative;
    border-left: 4px solid transparent;
}

.notification-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.07);
    border-color: #fed7aa;
}

.notification-card.unread {
    border-left: 4px solid #ea580c;
    background: #fff7ed;
}

.notification-card.unread:hover {
    background: #ffedd5;
}

.notif-icon-circle {
    display: flex;
    justify-content: center;
    align-items: center;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: rgba(234, 88, 12, 0.1);
    color: #ea580c;
    font-size: 1.15rem;
    flex-shrink: 0;
}

.notification-card.unread .notif-icon-circle {
    background: rgba(234, 88, 12, 0.2);
    color: #c2410c;
}

.notif-body {
    flex-grow: 1;
    min-width: 0;
}

.notif-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-start;
    margin-bottom: 6px;
    gap: 1rem;
}

.notif-subject {
    font-weight: 600;
    font-size: 1.025rem;
    color: #1f2937;
}

.notif-time {
    font-size: 0.785rem;
    color: #9ca3af;
    white-space: nowrap;
}

.notif-text {
    font-size: 0.875rem;
    color: #4b5563;
    line-height: 1.5;
    margin: 0;
}

.notification-card:not(.unread) .notif-subject {
    color: #4b5563;
}

.notification-card:not(.unread) .notif-text {
    color: #6b7280;
}

.empty-state {
    text-align: center;
    padding: 80px 20px;
    color: #6b7280;
    background: #ffffff;
    border: 1px solid #e5e7eb;
    border-radius: 20px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.03);
}

.empty-icon {
    font-size: 3.5rem;
    margin-bottom: 1.25rem;
    color: #d1d5db;
}

@media (max-width: 600px) {
    .notifications-header {
        flex-direction: column;
        align-items: flex-start;
        gap: 1rem;
    }
    
    .btn-mark-all {
        width: 100%;
        text-align: center;
    }
    
    .notification-card {
        padding: 1rem;
        gap: 0.75rem;
    }
    
    .notif-head {
        flex-direction: column;
        gap: 0.25rem;
    }
}
</style>

<div class="notifications-wrapper">
    <div class="notifications-header">
        <h1 class="notifications-title">Alert Center</h1>
        <button class="btn-mark-all" onclick="markAllAsRead()">Mark all as read</button>
    </div>

    <div class="notifications-list" id="fullNotificationsList">
        <?php
        $notif_q = mysqli_query($conn, "SELECT * FROM notifications WHERE user_id = '$user_id' ORDER BY created_at DESC LIMIT 50");
        if (mysqli_num_rows($notif_q) > 0):
            while ($notif = mysqli_fetch_assoc($notif_q)):
                $is_unread = !$notif['is_read'];
                $time_formatted = date('M d, Y - H:i', strtotime($notif['created_at']));
        ?>
            <div class="notification-card <?php echo $is_unread ? 'unread' : ''; ?>" data-id="<?php echo $notif['id']; ?>" onclick="readNotification(this, <?php echo $notif['id']; ?>)">
                <div class="notif-icon-circle">
                    <i class="fas fa-bell"></i>
                </div>
                <div class="notif-body">
                    <div class="notif-head">
                        <span class="notif-subject"><?php echo htmlspecialchars($notif['title']); ?></span>
                        <span class="notif-time"><?php echo $time_formatted; ?></span>
                    </div>
                    <p class="notif-text"><?php echo htmlspecialchars($notif['message']); ?></p>
                </div>
            </div>
        <?php 
            endwhile;
        else:
        ?>
            <div class="empty-state">
                <div class="empty-icon"><i class="fas fa-bell-slash"></i></div>
                <p style="font-size: 1rem; font-weight: 500; margin: 0;">No notifications yet!</p>
            </div>
        <?php endif; ?>
    </div>
</div>

<script>
function readNotification(card, id) {
    if (!card.classList.contains('unread')) return;
    
    // Dynamic base path resolve
    const base = window.location.pathname.substring(0, window.location.pathname.indexOf('/user/dashboard'));
    
    fetch(`${base}/user/dashboard/notifications/read.php?id=${id}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            card.classList.remove('unread');
            // Refresh unread count in header
            if (window.loadUnreadCount) window.loadUnreadCount();
        }
    })
    .catch(err => console.error('Error marking as read:', err));
}

function markAllAsRead() {
    const base = window.location.pathname.substring(0, window.location.pathname.indexOf('/user/dashboard'));
    fetch(`${base}/user/dashboard/notifications/read.php?id=all`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ id: 'all' })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.querySelectorAll('.notification-card.unread').forEach(card => {
                card.classList.remove('unread');
            });
            if (window.loadUnreadCount) window.loadUnreadCount();
        }
    })
    .catch(err => console.error('Error marking all as read:', err));
}
</script>

<?php include('../../../components/layout_bottom.php'); ?>
