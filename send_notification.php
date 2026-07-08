<?php
// api/send_notification.php
// Sends order confirmation notification via Email and/or WhatsApp
// Called after a successful order placement via checkout.php
require_once __DIR__ . '/../includes/db.php';

header('Content-Type: application/json');

// Only allow POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$orderId = intval($input['order_id'] ?? 0);
$channel = $input['channel'] ?? 'email'; // 'email', 'whatsapp', 'both'

if (!$orderId) {
    echo json_encode(['status' => 'error', 'message' => 'Order ID is required.']);
    exit;
}

// Fetch order details
$stmt = $pdo->prepare("SELECT o.*, u.email, u.name as user_name FROM orders o 
                        LEFT JOIN users u ON o.user_id = u.id 
                        WHERE o.id = ? LIMIT 1");
$stmt->execute([$orderId]);
$order = $stmt->fetch();

if (!$order) {
    echo json_encode(['status' => 'error', 'message' => 'Order not found.']);
    exit;
}

// Fetch restaurant settings
$restaurantName = getSetting('restaurant_name', 'Crispy Bytes');
$restaurantPhone = getSetting('contact_phone', '+91 95164 40137');
$restaurantEmail = getSetting('contact_email', 'info@crispybytes.com');

// =====================================================================
// Build the luxury notification message
// =====================================================================
$patronName = $order['customer_name'] ?? $order['user_name'] ?? 'Valued Patron';
$orderNo    = $order['order_no'];
$grandTotal = '₹' . number_format($order['grand_total'], 2);
$orderType  = ucfirst(str_replace('_', ' ', $order['order_type']));

$luxuryMessage = "Greeting {$patronName},\n\n"
    . "⚜️ Your Gourmet Order #{$orderNo} has been confirmed at {$restaurantName}.\n\n"
    . "🍳 Our elite chefs have started crafting your selection with the finest ingredients.\n"
    . "📦 Order Mode: {$orderType}\n"
    . "💰 Grand Total: {$grandTotal}\n\n"
    . "You will receive a dispatch notification once your order leaves our kitchen.\n\n"
    . "For concierge support:\n"
    . "📞 {$restaurantPhone}\n"
    . "📧 {$restaurantEmail}\n\n"
    . "Thank you for choosing {$restaurantName}. Bon Appétit! 🥂";

$results = [];

// =====================================================================
// EMAIL Notification (via PHP mail() — replace with PHPMailer/SMTP for production)
// =====================================================================
if (in_array($channel, ['email', 'both']) && !empty($order['email'])) {
    $toEmail   = $order['email'];
    $subject   = "⚜️ Order #{$orderNo} Confirmed | {$restaurantName}";
    $emailBody = "<!DOCTYPE html>
<html>
<head><meta charset='UTF-8'><meta name='viewport' content='width=device-width, initial-scale=1'></head>
<body style='margin:0;padding:0;font-family:\"Segoe UI\",Arial,sans-serif;background:#f4f4f0;'>
  <div style='max-width:580px;margin:40px auto;background:#ffffff;border-radius:20px;overflow:hidden;border:1px solid rgba(197,168,128,0.2);'>
    <!-- Header -->
    <div style='background:linear-gradient(135deg,#121212 0%,#2a2a2a 100%);padding:32px 40px;text-align:center;'>
      <h2 style='color:#C5A880;font-size:22px;margin:0;letter-spacing:-0.5px;'>⚜️ {$restaurantName}</h2>
      <p style='color:rgba(255,255,255,0.6);font-size:12px;margin:6px 0 0;letter-spacing:1.5px;text-transform:uppercase;'>Gourmet Cloud Kitchen · Jaipur</p>
    </div>
    <!-- Body -->
    <div style='padding:36px 40px;'>
      <p style='font-size:15px;color:#121212;font-weight:600;margin-bottom:6px;'>Greeting, {$patronName} 👋</p>
      <p style='font-size:13px;color:#555;line-height:1.7;'>Your order has been received and our elite chefs have begun crafting your selection with the finest ingredients.</p>
      <div style='background:#fafaf8;border:1px solid rgba(197,168,128,0.2);border-radius:14px;padding:20px 24px;margin:24px 0;'>
        <table width='100%' cellspacing='0' cellpadding='0'>
          <tr><td style='font-size:12px;color:#888;padding:4px 0;'>Order Token</td><td style='font-size:13px;font-weight:700;color:#121212;text-align:right;'>{$orderNo}</td></tr>
          <tr><td style='font-size:12px;color:#888;padding:4px 0;'>Assignment Mode</td><td style='font-size:13px;font-weight:600;color:#121212;text-align:right;'>{$orderType}</td></tr>
          <tr><td style='font-size:12px;color:#888;padding:4px 0;border-top:1px solid rgba(197,168,128,0.15);padding-top:12px;margin-top:8px;'>Sum Total</td><td style='font-size:18px;font-weight:800;color:#b08d5b;text-align:right;border-top:1px solid rgba(197,168,128,0.15);padding-top:12px;'>{$grandTotal}</td></tr>
        </table>
      </div>
      <p style='font-size:13px;color:#555;line-height:1.7;'>For immediate concierge assistance, contact us at <strong>{$restaurantPhone}</strong> or reply to this email.</p>
    </div>
    <!-- Footer -->
    <div style='background:#fafaf8;padding:20px 40px;text-align:center;border-top:1px solid rgba(197,168,128,0.15);'>
      <p style='font-size:11px;color:#999;margin:0;'>Thank you for ordering with {$restaurantName}. Bon Appétit! 🥂</p>
    </div>
  </div>
</body>
</html>";

    $headers  = "MIME-Version: 1.0\r\n";
    $headers .= "Content-type: text/html; charset=UTF-8\r\n";
    $headers .= "From: {$restaurantName} <{$restaurantEmail}>\r\n";
    $headers .= "Reply-To: {$restaurantEmail}\r\n";

    $sent = mail($toEmail, $subject, $emailBody, $headers);
    $results['email'] = $sent ? 'sent' : 'failed';
}

// =====================================================================
// WHATSAPP Notification (via Meta/Twilio WhatsApp API or Ultramsg)
// Configure your API credentials below to activate.
// =====================================================================
if (in_array($channel, ['whatsapp', 'both']) && !empty($order['customer_phone'])) {
    $customerPhone = preg_replace('/[^0-9]/', '', $order['customer_phone']);

    // --- CONFIGURATION (fill in your provider credentials) ---
    $whatsappProvider = 'ultramsg'; // Options: 'ultramsg' | 'twilio'
    
    // Ultramsg.com setup
    $ultraInstanceId  = 'YOUR_ULTRAMSG_INSTANCE_ID';   // e.g. instance12345
    $ultraToken       = 'YOUR_ULTRAMSG_API_TOKEN';

    // Twilio setup (alternative)
    $twilioSid     = 'YOUR_TWILIO_ACCOUNT_SID';
    $twilioToken   = 'YOUR_TWILIO_AUTH_TOKEN';
    $twilioFrom    = 'whatsapp:+14155238886'; // Twilio sandbox number

    if ($whatsappProvider === 'ultramsg') {
        // Ultramsg WhatsApp API call
        $waPayload = http_build_query([
            'token'   => $ultraToken,
            'to'      => '91' . $customerPhone, // Country code prefix
            'body'    => $luxuryMessage,
        ]);
        $waContext = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
                'content' => $waPayload,
                'timeout' => 10,
            ]
        ]);
        $waResponse = @file_get_contents(
            "https://api.ultramsg.com/{$ultraInstanceId}/messages/chat",
            false,
            $waContext
        );
        $results['whatsapp'] = $waResponse ? 'sent' : 'failed (check credentials)';

    } elseif ($whatsappProvider === 'twilio') {
        // Twilio WhatsApp API call
        $twilioPayload = http_build_query([
            'From' => $twilioFrom,
            'To'   => 'whatsapp:+91' . $customerPhone,
            'Body' => $luxuryMessage,
        ]);
        $twilioContext = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Authorization: Basic " . base64_encode("{$twilioSid}:{$twilioToken}") . "\r\nContent-Type: application/x-www-form-urlencoded\r\n",
                'content' => $twilioPayload,
                'timeout' => 10,
            ]
        ]);
        $twilioResponse = @file_get_contents(
            "https://api.twilio.com/2010-04-01/Accounts/{$twilioSid}/Messages.json",
            false,
            $twilioContext
        );
        $results['whatsapp'] = $twilioResponse ? 'sent' : 'failed (check credentials)';
    }
}

// =====================================================================
// Return results
// =====================================================================
$allSuccess = !in_array('failed', $results) && !empty($results);
echo json_encode([
    'status'  => $allSuccess ? 'success' : 'partial',
    'message' => 'Notification dispatch attempted.',
    'results' => $results,
    'order'   => $orderNo,
]);
