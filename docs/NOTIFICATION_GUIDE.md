# IT Quty - Notification System Guide

## Overview
The IT Quty system includes a comprehensive notification system that sends email alerts for tickets and meeting room bookings. The system has two levels of control:

1. **System-wide Settings** (Super Admin only)
2. **User Preferences** (All users)

---

## For Super Admins

### Accessing System Settings
URL: `http://192.168.1.87/admin/notification-settings`

### System-wide Controls
These settings control whether notifications are enabled globally:

**Email Notifications:**
- ✓ Enable/Disable Email System
- ✓ Meeting Room Approval Notifications
- ✓ Meeting Room Rejection Notifications  
- ✓ Ticket Created Notifications
- ✓ Ticket Assigned Notifications
- ✓ Ticket Updated Notifications

**WhatsApp Notifications (Future):**
- Configuration for WhatsApp API
- Enable/Disable WhatsApp System

**Telegram Notifications (Future):**
- Configuration for Telegram Bot
- Enable/Disable Telegram System

### Important Notes:
- If system-wide email is **disabled**, NO users will receive email notifications
- If system-wide email is **enabled**, users can still control their own preferences
- Changes are cached for performance - clear cache if needed

---

## For All Users

### Accessing User Preferences
URL: `http://192.168.1.87/profile/notifications`

Navigation: Click your name → Profile → Notifications

### Personal Notification Controls

**Master Email Toggle:**
- **Enabled**: Receive all email notifications (based on your preferences below)
- **Disabled**: Stop ALL email notifications to your account

**Ticket Notifications:**
- ☑ Ticket Created - When you create a new ticket
- ☑ Ticket Assigned - When a ticket is assigned to you
- ☑ Ticket Updated - When your ticket status/priority changes

**Meeting Room Notifications:**
- ☑ Meeting Approved - When your booking is approved
- ☑ Meeting Rejected - When your booking is rejected

### How It Works:

**To receive a notification, BOTH conditions must be true:**
1. System setting must be **enabled** (by admin)
2. Your personal preference must be **enabled**

**Examples:**

✅ **You WILL receive email if:**
- System Email = Enabled
- Master Email = Enabled  
- Specific notification type = Enabled

❌ **You WON'T receive email if:**
- System Email = Disabled (admin turned it off)
- Master Email = Disabled (you turned off all emails)
- Specific notification type = Unchecked (you opted out)

---

## Notification Types

### 1. Ticket Created
**Sent to:** Ticket creator  
**When:** Immediately after ticket creation  
**Contains:**
- Ticket number and title
- Priority and status
- Description
- Link to view ticket

### 2. Ticket Assigned
**Sent to:** Assigned user  
**When:** When ticket is assigned to them  
**Contains:**
- Ticket details
- Who assigned it
- Priority level
- Link to view ticket

### 3. Ticket Updated
**Sent to:** Ticket creator  
**When:** Status or priority changes  
**Contains:**
- Updated status/priority
- Who made the change
- Current ticket details
- Link to view ticket

### 4. Meeting Room Approved
**Sent to:** Booking requester  
**When:** Admin approves booking  
**Contains:**
- Meeting room name
- Booking date and time
- Duration
- Link to view booking

### 5. Meeting Room Rejected
**Sent to:** Booking requester  
**When:** Admin rejects booking  
**Contains:**
- Meeting room name
- Requested date and time
- Rejection reason (if provided)
- Link to make new booking

---

## Email Configuration

**SMTP Server:** mx.quty.co.id:465 (SSL)  
**From Address:** ims.it@quty.co.id  
**From Name:** IMSQuty

All emails are sent from the official company email address.

---

## Testing Notifications

### Test Script Available
Run: `php send-test-email.php` (from server)

This will:
- Send test email to all users
- Show delivery status for each user
- Report any failures

### Manual Testing

**Test Ticket Notifications:**
1. Create a new ticket at: `http://192.168.1.87/tickets/create`
2. Check your email inbox
3. Assign the ticket to another user
4. Check that user's inbox
5. Update ticket status
6. Check creator's inbox

**Test Meeting Room Notifications:**
1. Book a meeting room at: `http://192.168.1.87/meeting-rooms`
2. Admin approves/rejects at: `http://192.168.1.87/admin/meeting-rooms/approval`
3. Check requester's inbox

---

## Troubleshooting

### "I'm not receiving emails"

**Check these in order:**

1. **System Settings** (Super Admin only)
   - Go to: `http://192.168.1.87/admin/notification-settings`
   - Verify "Enable Email Notifications" is ON
   - Verify specific notification types are enabled

2. **Your Preferences**
   - Go to: `http://192.168.1.87/profile/notifications`
   - Verify "Master Email Notification" is ENABLED
   - Verify specific notification types are checked

3. **Email Address**
   - Verify your email address is correct in your profile
   - Check spam/junk folder

4. **Server Status**
   - Contact IT if emails still not arriving
   - SMTP server may be down

### "How do I stop all notifications?"

**Option 1: Turn off Master Toggle**
- Go to Profile → Notifications
- Click "Disabled" button
- Save Preferences

**Option 2: Uncheck Specific Types**
- Keep Master Toggle enabled
- Uncheck individual notification types
- Save Preferences

### "Can I receive some notifications but not others?"

Yes! Keep your Master Email Toggle **enabled**, then check/uncheck individual notification types:
- Want ticket notifications only? Uncheck meeting room boxes
- Want approvals only? Uncheck ticket boxes
- Full control over what you receive

---

## Future Features

### WhatsApp Notifications (Planned)
- Instant WhatsApp messages for urgent notifications
- Same dual-layer control system
- Requires phone number in profile

### Telegram Notifications (Planned)
- Telegram bot integration
- Real-time notifications
- Requires Telegram username

---

## Support

For issues with the notification system:
- **Technical Issues:** Contact IT Department
- **Email Delivery:** Check with IT regarding SMTP server
- **Feature Requests:** Submit a ticket

---

**Last Updated:** December 5, 2025  
**System Version:** IT Quty v2.0  
**Notification System:** Fully Operational ✓
