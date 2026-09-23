<?php

namespace App\Modules\Subscriptions\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Subscriptions\Models\Plan;
use Illuminate\Http\Request;

class PackageExperimentController extends Controller
{
    /**
     * Display the experimental packages & add-ons prototype.
     */
    public function index()
    {
        if (!config('app.package_experiment', true)) {
            abort(404, 'Package experiment sandbox is currently disabled.');
        }

        $packages = $this->getExperimentalPackages();
        $addons = $this->getExperimentalAddons();
        $comparisonMatrix = $this->getComparisonMatrix();
        $presets = $this->getPresets();

        // Read-only reference of existing production plans for comparative evaluation
        $currentProductionPlans = Plan::all(['id', 'name', 'slug', 'price', 'annual_price', 'limits', 'is_active']);

        return view('experimental.packages', compact(
            'packages',
            'addons',
            'comparisonMatrix',
            'presets',
            'currentProductionPlans'
        ));
    }

    /**
     * Get the 3 experimental core packages definition.
     */
    protected function getExperimentalPackages(): array
    {
        return [
            'simple' => [
                'id' => 'simple',
                'name' => 'Noubtigo Simple',
                'badge' => 'Basic Queues',
                'tagline' => 'Just manage the line.',
                'summary' => 'For businesses that only need basic queue management without the overhead of customer information.',
                'ideal_for' => 'Fast-food counters, pharmacy pick-up, retail returns, single-counter service.',
                'color' => '#059669',
                'bg_gradient' => 'linear-gradient(135deg, #10b981 0%, #059669 100%)',
                'accent_color' => '#10b981',
                'capabilities' => [
                    'Create ticket with instant QR',
                    'Ticket number generation',
                    'Call / Next ticket action',
                    'Pass / Skip ticket action',
                    'Basic queue status management',
                    'Basic staff queue interface',
                    'QR code ticket tracking',
                    'Zero customer data collection required',
                ],
                'boundary_note' => 'No customer profiles, no multi-service hierarchy, no appointments.',
            ],
            'queue' => [
                'id' => 'queue',
                'name' => 'Noubtigo Queue',
                'badge' => 'Customers + Queues',
                'tagline' => 'Manage customers and queues.',
                'summary' => 'For businesses that need rich customer records, multiple queues, staff coordination, and service routing.',
                'ideal_for' => 'Salons, repair shops, banking desks, telecom branches, public offices.',
                'color' => '#0284c7',
                'bg_gradient' => 'linear-gradient(135deg, #0284c7 0%, #0369a1 100%)',
                'accent_color' => '#0284c7',
                'popular' => true,
                'capabilities' => [
                    'Everything included in Simple',
                    'Customer profiles & contact directory',
                    'Customer name, phone & notes',
                    'Multi-service department routing',
                    'Multiple staff accounts & roles',
                    'Multiple concurrent queues',
                    'Customer visit history timeline',
                    'Queue history & audit records',
                    'Priority customer flags & fast-track',
                    'Live customer wait tracking',
                    'Basic operational reports & metrics',
                ],
                'boundary_note' => 'Includes full customer CRM and queueing, but no appointment scheduling.',
            ],
            'queue_plus' => [
                'id' => 'queue_plus',
                'name' => 'Noubtigo Queue+',
                'badge' => 'Queues + Appointments',
                'tagline' => 'Appointments meet queues.',
                'summary' => 'For businesses that need unified management of pre-booked appointments alongside real-time walk-in queues.',
                'ideal_for' => 'Medical clinics, wellness centers, advisory firms, hybrid walk-in/scheduled clinics.',
                'color' => '#7c3aed',
                'bg_gradient' => 'linear-gradient(135deg, #8b5cf6 0%, #6d28d9 100%)',
                'accent_color' => '#7c3aed',
                'capabilities' => [
                    'Everything included in Queue',
                    'Full appointment booking engine',
                    'Interactive staff & room calendar',
                    'Customizable time slots & capacities',
                    'Rooms & physical resource allocation',
                    'Seamless appointment check-in to queue',
                    'Integrated appointment + queue merging',
                    'Smart appointment sequencing & priority',
                    'No-show & cancellation lifecycle management',
                    'Comprehensive appointment logs & history',
                ],
                'boundary_note' => 'The ultimate unified queueing and scheduling suite.',
            ],
        ];
    }

    /**
     * Get the 5 modular optional add-ons definition.
     */
    protected function getExperimentalAddons(): array
    {
        return [
            'whatsapp' => [
                'id' => 'whatsapp',
                'name' => 'WhatsApp Add-on',
                'icon' => 'bi-whatsapp',
                'color' => '#25D366',
                'category' => 'Messaging & Engagement',
                'headline' => 'Direct queue updates to customer WhatsApp chats',
                'description' => 'Keeps customers updated without forcing them to stay glued to physical screens.',
                'capabilities' => [
                    'Send digital ticket details via WhatsApp',
                    'Notify customer automatically when approaching their turn',
                    'Immediate "You are being called" alert',
                    'Interactive queue tracking link sent on WhatsApp',
                ],
            ],
            'display' => [
                'id' => 'display',
                'name' => 'Display Add-on',
                'icon' => 'bi-display',
                'color' => '#0ea5e9',
                'category' => 'Hardware & Screens',
                'headline' => 'Waiting room TV screens & digital signage',
                'description' => 'Pair smart TVs, monitors, or tablets with room-specific ticket calling displays.',
                'capabilities' => [
                    'Waiting room full-screen calling display',
                    'Current ticket & serving counter display with chime audio',
                    'Upcoming tickets in line ticker/list',
                    'Multiple display screens with independent room assignments',
                    'Custom media ticker, announcements & branding slides',
                ],
            ],
            'notifications' => [
                'id' => 'notifications',
                'name' => 'Notifications Add-on',
                'icon' => 'bi-bell',
                'color' => '#f59e0b',
                'category' => 'Multi-channel Alerts',
                'headline' => 'Automated SMS, Email & Web Push notifications',
                'description' => 'Guarantee customers never miss their turn with multi-channel failover alerts.',
                'capabilities' => [
                    'SMS ticket delivery & calling alerts',
                    'Email booking confirmations & receipts',
                    'Real-time web & browser push notifications',
                    'Configurable automated notification triggers',
                ],
            ],
            'analytics' => [
                'id' => 'analytics',
                'name' => 'Advanced Analytics Add-on',
                'icon' => 'bi-graph-up-arrow',
                'color' => '#6366f1',
                'category' => 'Business Intelligence',
                'headline' => 'Deep queue analytics, peak hours & SLA metrics',
                'description' => 'Uncover operational bottlenecks, staff throughput, and customer wait patterns.',
                'capabilities' => [
                    'Comprehensive multi-dimensional performance reports',
                    'Peak hours & traffic heat map analysis',
                    'Average wait & service time breakdown per agent/service',
                    'Historical longitudinal trends & CSV/Excel exports',
                    'Staff productivity benchmarking & SLA monitoring',
                ],
            ],
            'api' => [
                'id' => 'api',
                'name' => 'API / Integrations Add-on',
                'icon' => 'bi-cpu',
                'color' => '#ec4899',
                'category' => 'Integrations & Developers',
                'headline' => 'REST APIs, Webhooks & 3rd-party ERP connections',
                'description' => 'Embed Noubtigo into existing hospital systems, CRM tools, or proprietary kiosks.',
                'capabilities' => [
                    'Full REST API access for ticket & queue lifecycle',
                    'Real-time Webhook event dispatches on call/done/create',
                    'External CRM, ERP & POS integration connectors',
                    'Custom automated background data sync',
                ],
            ],
        ];
    }

    /**
     * Comparison matrix cross-referencing capabilities across packages and add-ons.
     */
    protected function getComparisonMatrix(): array
    {
        return [
            [
                'category' => 'Queue Core & Ticketing',
                'items' => [
                    ['name' => 'Ticket Generation & Numbering', 'simple' => 'Core', 'queue' => 'Core', 'queue_plus' => 'Core', 'addon' => null],
                    ['name' => 'Call / Next & Skip / Pass Actions', 'simple' => 'Core', 'queue' => 'Core', 'queue_plus' => 'Core', 'addon' => null],
                    ['name' => 'QR Code Mobile Ticket Tracking', 'simple' => 'Core', 'queue' => 'Core', 'queue_plus' => 'Core', 'addon' => null],
                    ['name' => 'Anonymous Ticketing (No customer info)', 'simple' => 'Core', 'queue' => 'Core', 'queue_plus' => 'Core', 'addon' => null],
                ],
            ],
            [
                'category' => 'Customer Management & Routing',
                'items' => [
                    ['name' => 'Customer Profiles & Phone Directory', 'simple' => false, 'queue' => 'Core', 'queue_plus' => 'Core', 'addon' => null],
                    ['name' => 'Multiple Service Categories & Desks', 'simple' => false, 'queue' => 'Core', 'queue_plus' => 'Core', 'addon' => null],
                    ['name' => 'Multiple Staff Members & Roles', 'simple' => false, 'queue' => 'Core', 'queue_plus' => 'Core', 'addon' => null],
                    ['name' => 'Multiple Concurrent Queues', 'simple' => false, 'queue' => 'Core', 'queue_plus' => 'Core', 'addon' => null],
                    ['name' => 'Customer Visit & Queue History', 'simple' => false, 'queue' => 'Core', 'queue_plus' => 'Core', 'addon' => null],
                    ['name' => 'Priority / VIP Fast-Track Customers', 'simple' => false, 'queue' => 'Core', 'queue_plus' => 'Core', 'addon' => null],
                    ['name' => 'Basic Operational Statistics', 'simple' => false, 'queue' => 'Core', 'queue_plus' => 'Core', 'addon' => null],
                ],
            ],
            [
                'category' => 'Appointments & Calendars',
                'items' => [
                    ['name' => 'Appointment Scheduling & Booking', 'simple' => false, 'queue' => false, 'queue_plus' => 'Core', 'addon' => null],
                    ['name' => 'Interactive Staff/Room Calendar View', 'simple' => false, 'queue' => false, 'queue_plus' => 'Core', 'addon' => null],
                    ['name' => 'Custom Time Slots & Capacity Rules', 'simple' => false, 'queue' => false, 'queue_plus' => 'Core', 'addon' => null],
                    ['name' => 'Rooms & Physical Asset Allocation', 'simple' => false, 'queue' => false, 'queue_plus' => 'Core', 'addon' => null],
                    ['name' => 'One-Click Appointment Check-in to Queue', 'simple' => false, 'queue' => false, 'queue_plus' => 'Core', 'addon' => null],
                    ['name' => 'Hybrid Walk-in & Appointment Merging', 'simple' => false, 'queue' => false, 'queue_plus' => 'Core', 'addon' => null],
                    ['name' => 'No-show & Cancellation Workflows', 'simple' => false, 'queue' => false, 'queue_plus' => 'Core', 'addon' => null],
                ],
            ],
            [
                'category' => 'Modular Add-ons (Optional for Any Package)',
                'items' => [
                    ['name' => 'WhatsApp Ticket & Call Notifications', 'simple' => 'Optional', 'queue' => 'Optional', 'queue_plus' => 'Optional', 'addon' => 'WhatsApp Add-on'],
                    ['name' => 'Waiting Room TV Screens & Digital Signage', 'simple' => 'Optional', 'queue' => 'Optional', 'queue_plus' => 'Optional', 'addon' => 'Display Add-on'],
                    ['name' => 'Multi-channel SMS, Email & Push Alerts', 'simple' => 'Optional', 'queue' => 'Optional', 'queue_plus' => 'Optional', 'addon' => 'Notifications Add-on'],
                    ['name' => 'Deep Analytics, Heat Maps & SLA Metrics', 'simple' => 'Optional', 'queue' => 'Optional', 'queue_plus' => 'Optional', 'addon' => 'Analytics Add-on'],
                    ['name' => 'REST APIs, Webhooks & ERP Connectors', 'simple' => 'Optional', 'queue' => 'Optional', 'queue_plus' => 'Optional', 'addon' => 'API Add-on'],
                ],
            ],
        ];
    }

    /**
     * Business presets for testing realistic bundles.
     */
    protected function getPresets(): array
    {
        return [
            [
                'id' => 'preset_retail',
                'title' => 'Quick-Service Retail / Bakery',
                'description' => 'Fast ticket dispensing, no customer info needed, visual TV calling in waiting area.',
                'package' => 'simple',
                'addons' => ['display'],
            ],
            [
                'id' => 'preset_salon',
                'title' => 'Barbershop / Beauty Salon',
                'description' => 'Tracks client names, multiple stylists/services, sends WhatsApp alerts so clients can wait nearby.',
                'package' => 'queue',
                'addons' => ['whatsapp'],
            ],
            [
                'id' => 'preset_clinic',
                'title' => 'Specialized Medical Clinic',
                'description' => 'Pre-booked time slots, consultation rooms, check-in to queue, WhatsApp notifications.',
                'package' => 'queue_plus',
                'addons' => ['whatsapp', 'display', 'notifications'],
            ],
            [
                'id' => 'preset_enterprise',
                'title' => 'Enterprise Bank / Public Service',
                'description' => 'Hybrid appointments & queues, multi-screens, full analytics and REST API ERP integration.',
                'package' => 'queue_plus',
                'addons' => ['whatsapp', 'display', 'notifications', 'analytics', 'api'],
            ],
        ];
    }
}
