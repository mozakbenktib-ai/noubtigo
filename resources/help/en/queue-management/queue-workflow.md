---
title: Queue Management & Ticket Workflows
category: Queue Management
description: Comprehensive guide explaining Simple vs Advanced Queue modes, calling next, hold, pass, cancel, and ticket lifecycle.
roles: [admin, secretary, staff, customer]
routes: [queue.index, queue.simple.index, tickets.index, tickets.show]
video_url: https://www.youtube.com/embed/placeholder
---

# Queue Management & Ticket Workflows

Noubtigo provides an intuitive queue operating system supporting two operational modes tailored to your business: **Simple Queue Mode** and **Advanced Queue Mode**.

---

## Queue Modes Explained

| Feature | Simple Queue Mode | Advanced Queue Mode |
| :--- | :--- | :--- |
| **Ideal For** | Single-counter, quick walk-in counters, fast service | Multi-room clinics, enterprise desks, complex routing |
| **Workflows** | Next, Pass, Complete | Next, Hold, Resume, Pass, Transfer, Priority, Reopen |
| **VIP / Priority** | Basic | Advanced priority scoring & appointment integration |
| **Ticket Customization** | Instant quick dispatch | Detailed customer info, custom notes & multi-service |

---

## Ticket Lifecycle Workflow

<div class="ticket-lifecycle" role="img" aria-label="Ticket lifecycle from customer arrival to completion, with paths for absent customers and held tickets">
  <div class="ticket-lifecycle-main">
    <div class="ticket-stage stage-arrival"><span class="stage-number">1</span><i class="bi bi-person-walking"></i><strong>Arrival</strong><small>Customer checks in</small></div>
    <div class="stage-arrow"><i class="bi bi-arrow-right"></i></div>
    <div class="ticket-stage stage-waiting"><span class="stage-number">2</span><i class="bi bi-ticket-perforated"></i><strong>Waiting</strong><small>Ticket is issued</small></div>
    <div class="stage-arrow"><i class="bi bi-arrow-right"></i></div>
    <div class="ticket-stage stage-called"><span class="stage-number">3</span><i class="bi bi-megaphone"></i><strong>Called</strong><small>Go to the counter</small></div>
    <div class="stage-arrow"><i class="bi bi-arrow-right"></i></div>
    <div class="ticket-stage stage-serving"><span class="stage-number">4</span><i class="bi bi-person-check"></i><strong>Serving</strong><small>Service in progress</small></div>
    <div class="stage-arrow"><i class="bi bi-arrow-right"></i></div>
    <div class="ticket-stage stage-complete"><span class="stage-number">5</span><i class="bi bi-check2-circle"></i><strong>Completed</strong><small>Visit finished</small></div>
  </div>
  <div class="ticket-lifecycle-branches">
    <div class="lifecycle-branch branch-absent"><i class="bi bi-person-x"></i><div><strong>Customer absent?</strong><span>Use <b>Pass</b> or <b>Cancel</b>.</span></div></div>
    <div class="lifecycle-branch branch-hold"><i class="bi bi-pause-circle"></i><div><strong>Need more time?</strong><span>Put on <b>Hold</b>, then <b>Resume</b>.</span></div></div>
  </div>
</div>

### Action Controls

1. **Issue Ticket (`+ New Ticket`)**: Generates a unique sequential ticket number (e.g. `A-014`) based on service prefix.
2. **Call Next (`Call Next`)**: Triggers visual & sound alert on public display screens and sends WhatsApp / SMS notification if configured.
3. **Pass / Missed (`Pass`)**: Used when a customer is not present when called. Keeps record and moves to next customer.
4. **Put On Hold (`Hold`)**: Temporarily pauses serving a customer (e.g. while they gather missing documents) without cancelling their ticket.
5. **Resume (`Resume`)**: Returns a held customer back to the priority waiting state.
6. **Cancel / Reopen (`Cancel` / `Reopen`)**: Cancels invalid tickets or restores accidentally cancelled tickets.

---

## Customer Tracking

Customers can track their position in live time using:
- **QR Code on printed ticket**: Scanned with mobile camera.
- **WhatsApp Notification**: Live link to Customer Tracking Portal.
- **Public TV Display**: Display screen configured per room.
