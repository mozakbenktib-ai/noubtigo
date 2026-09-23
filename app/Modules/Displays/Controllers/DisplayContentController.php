<?php

namespace App\Modules\Displays\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\Displays\Models\DisplayContent;
use App\Modules\Displays\Models\DisplayDevice;
use App\Modules\Displays\Services\DisplayContentService;
use App\Services\TenantManager;
use App\Services\TimezoneService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DisplayContentController extends Controller
{
    protected DisplayContentService $contentService;
    protected TimezoneService $timezoneService;

    public function __construct(DisplayContentService $contentService, TimezoneService $timezoneService)
    {
        $this->contentService = $contentService;
        $this->timezoneService = $timezoneService;
    }

    /**
     * Admin: List all display contents for tenant.
     */
    public function index(Request $request)
    {
        $tenantId = app(TenantManager::class)->getTenantId();
        if (!$tenantId) {
            abort(403, 'Tenant context missing.');
        }

        $query = DisplayContent::with(['devices', 'creator'])
            ->where('company_id', $tenantId);

        // Filter by Type
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        // Filter by Target
        if ($request->filled('target_type')) {
            $query->where('target_type', $request->target_type);
        }

        // Filter by Status
        $nowUtc = now('UTC');
        if ($request->status === 'active') {
            $query->where('is_active', true)
                  ->where(fn($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $nowUtc))
                  ->where(fn($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $nowUtc));
        } elseif ($request->status === 'inactive') {
            $query->where('is_active', false);
        } elseif ($request->status === 'scheduled') {
            $query->where('is_active', true)
                  ->whereNotNull('starts_at')
                  ->where('starts_at', '>', $nowUtc);
        } elseif ($request->status === 'expired') {
            $query->where('is_active', true)
                  ->whereNotNull('ends_at')
                  ->where('ends_at', '<', $nowUtc);
        }

        $contents = $query->orderBy('sort_order', 'asc')
            ->orderBy('created_at', 'desc')
            ->paginate(15)
            ->withQueryString();

        // Overall stats for summary badges
        $totalCount = DisplayContent::where('company_id', $tenantId)->count();
        $activeCount = DisplayContent::where('company_id', $tenantId)->where('is_active', true)->count();
        $globalCount = DisplayContent::where('company_id', $tenantId)->where('target_type', DisplayContent::TARGET_ALL)->count();
        $assignedCount = DisplayContent::where('company_id', $tenantId)->where('target_type', DisplayContent::TARGET_SELECTED)->count();

        // Device content availability stats
        $devices = DisplayDevice::with('room')
            ->where('company_id', $tenantId)
            ->orderBy('name', 'asc')
            ->get();
        $deviceStats = $this->contentService->getDeviceContentCounts($tenantId);

        return view('displays.contents.index', compact(
            'contents',
            'totalCount',
            'activeCount',
            'globalCount',
            'assignedCount',
            'devices',
            'deviceStats'
        ));
    }

    /**
     * Admin: Show form to create display content.
     */
    public function create()
    {
        $tenantId = app(TenantManager::class)->getTenantId();
        $devices = DisplayDevice::with('room')
            ->where('company_id', $tenantId)
            ->orderBy('name', 'asc')
            ->get();

        $types = DisplayContent::getTypes();

        return view('displays.contents.create', compact('devices', 'types'));
    }

    /**
     * Admin: Store new display content.
     */
    public function store(Request $request)
    {
        $tenantId = app(TenantManager::class)->getTenantId();

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|string|in:information,promotion,qr_tracking,announcement,video,carousel,emergency_notice',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'link_url' => 'nullable|url|max:500',
            'qr_url' => 'nullable|url|max:500',
            'target_type' => 'required|string|in:all,selected',
            'device_ids' => 'nullable|array',
            'device_ids.*' => 'integer',
            'duration' => 'required|integer|min:3|max:300',
            'sort_order' => 'nullable|integer|min:0',
            'priority' => 'nullable|string|in:normal,high,emergency',
            'is_active' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        // Upload Image
        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('display-contents', 'public');
        }

        // Parse Local Times to UTC
        $startsAt = null;
        if (!empty($validated['starts_at'])) {
            $startsAt = $this->timezoneService->toUTC($validated['starts_at']);
        }

        $endsAt = null;
        if (!empty($validated['ends_at'])) {
            $endsAt = $this->timezoneService->toUTC($validated['ends_at']);
        }

        /** @var DisplayContent $content */
        $content = DisplayContent::create([
            'company_id' => $tenantId,
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'image_path' => $imagePath,
            'link_url' => $validated['link_url'] ?? null,
            'qr_url' => $validated['qr_url'] ?? null,
            'target_type' => $validated['target_type'],
            'duration' => $validated['duration'] ?? 10,
            'sort_order' => $validated['sort_order'] ?? 0,
            'priority' => $validated['priority'] ?? DisplayContent::PRIORITY_NORMAL,
            'is_active' => $request->boolean('is_active', true),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'created_by' => auth()->id(),
        ]);

        // Sync Display Assignments
        if ($validated['target_type'] === DisplayContent::TARGET_SELECTED) {
            $this->contentService->syncDisplayAssignments(
                $content,
                $request->input('device_ids', []),
                $tenantId
            );
        }

        return redirect()->route('displays.contents.index')
            ->with('success', __('ui.display_content_created') ?: 'Display content item created successfully.');
    }

    /**
     * Admin: Show edit form for display content.
     */
    public function edit(DisplayContent $content)
    {
        $tenantId = app(TenantManager::class)->getTenantId();
        if ($content->company_id !== $tenantId) {
            abort(403);
        }

        $devices = DisplayDevice::with('room')
            ->where('company_id', $tenantId)
            ->orderBy('name', 'asc')
            ->get();

        $types = DisplayContent::getTypes();
        $assignedDeviceIds = $content->devices->pluck('id')->toArray();

        // Convert UTC dates to local for input fields
        $localStartsAt = $content->starts_at
            ? $this->timezoneService->toLocal($content->starts_at)->format('Y-m-d\TH:i')
            : null;

        $localEndsAt = $content->ends_at
            ? $this->timezoneService->toLocal($content->ends_at)->format('Y-m-d\TH:i')
            : null;

        return view('displays.contents.edit', compact(
            'content',
            'devices',
            'types',
            'assignedDeviceIds',
            'localStartsAt',
            'localEndsAt'
        ));
    }

    /**
     * Admin: Update display content.
     */
    public function update(Request $request, DisplayContent $content)
    {
        $tenantId = app(TenantManager::class)->getTenantId();
        if ($content->company_id !== $tenantId) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'required|string|in:information,promotion,qr_tracking,announcement,video,carousel,emergency_notice',
            'image' => 'nullable|image|mimes:jpeg,png,jpg,webp,gif|max:5120',
            'remove_image' => 'nullable|boolean',
            'link_url' => 'nullable|url|max:500',
            'qr_url' => 'nullable|url|max:500',
            'target_type' => 'required|string|in:all,selected',
            'device_ids' => 'nullable|array',
            'device_ids.*' => 'integer',
            'duration' => 'required|integer|min:3|max:300',
            'sort_order' => 'nullable|integer|min:0',
            'priority' => 'nullable|string|in:normal,high,emergency',
            'is_active' => 'nullable|boolean',
            'starts_at' => 'nullable|date',
            'ends_at' => 'nullable|date|after_or_equal:starts_at',
        ]);

        // Handle Image upload or removal
        $imagePath = $content->image_path;
        if ($request->boolean('remove_image') && $imagePath) {
            Storage::disk('public')->delete($imagePath);
            $imagePath = null;
        }

        if ($request->hasFile('image')) {
            if ($content->image_path) {
                Storage::disk('public')->delete($content->image_path);
            }
            $imagePath = $request->file('image')->store('display-contents', 'public');
        }

        // Parse Local Times to UTC
        $startsAt = null;
        if (!empty($validated['starts_at'])) {
            $startsAt = $this->timezoneService->toUTC($validated['starts_at']);
        }

        $endsAt = null;
        if (!empty($validated['ends_at'])) {
            $endsAt = $this->timezoneService->toUTC($validated['ends_at']);
        }

        $content->update([
            'title' => $validated['title'],
            'description' => $validated['description'] ?? null,
            'type' => $validated['type'],
            'image_path' => $imagePath,
            'link_url' => $validated['link_url'] ?? null,
            'qr_url' => $validated['qr_url'] ?? null,
            'target_type' => $validated['target_type'],
            'duration' => $validated['duration'] ?? 10,
            'sort_order' => $validated['sort_order'] ?? 0,
            'priority' => $validated['priority'] ?? DisplayContent::PRIORITY_NORMAL,
            'is_active' => $request->boolean('is_active', true),
            'starts_at' => $startsAt,
            'ends_at' => $endsAt,
            'updated_by' => auth()->id(),
        ]);

        // Sync Display Assignments
        $deviceIds = ($validated['target_type'] === DisplayContent::TARGET_SELECTED)
            ? $request->input('device_ids', [])
            : [];

        $this->contentService->syncDisplayAssignments($content, $deviceIds, $tenantId);

        return redirect()->route('displays.contents.index')
            ->with('success', __('ui.display_content_updated') ?: 'Display content updated successfully.');
    }

    /**
     * Admin: Delete display content.
     */
    public function destroy(DisplayContent $content)
    {
        $tenantId = app(TenantManager::class)->getTenantId();
        if ($content->company_id !== $tenantId) {
            abort(403);
        }

        if ($content->image_path) {
            Storage::disk('public')->delete($content->image_path);
        }

        $content->delete();

        return redirect()->route('displays.contents.index')
            ->with('success', __('ui.display_content_deleted') ?: 'Display content removed successfully.');
    }

    /**
     * Admin: Toggle active / inactive status.
     */
    public function toggle(DisplayContent $content)
    {
        $tenantId = app(TenantManager::class)->getTenantId();
        if ($content->company_id !== $tenantId) {
            abort(403);
        }

        $content->is_active = !$content->is_active;
        $content->updated_by = auth()->id();
        $content->save();

        $msg = $content->is_active 
            ? (__('ui.content_activated') ?: 'Content activated.') 
            : (__('ui.content_deactivated') ?: 'Content deactivated.');

        if (request()->expectsJson()) {
            return response()->json([
                'success' => true,
                'is_active' => $content->is_active,
                'message' => $msg,
            ]);
        }

        return redirect()->back()->with('success', $msg);
    }

    /**
     * Admin: Duplicate a content item.
     */
    public function duplicate(DisplayContent $content)
    {
        $tenantId = app(TenantManager::class)->getTenantId();
        if ($content->company_id !== $tenantId) {
            abort(403);
        }

        /** @var DisplayContent $replica */
        $replica = $content->replicate(['created_at', 'updated_at', 'deleted_at']);
        $replica->title = $content->title . ' (Copy)';
        $replica->is_active = false; // default inactive for safety
        $replica->created_by = auth()->id();
        $replica->updated_by = null;
        $replica->save();

        if ($content->target_type === DisplayContent::TARGET_SELECTED) {
            $replica->devices()->sync($content->devices->pluck('id')->toArray());
        }

        return redirect()->route('displays.contents.edit', $replica->id)
            ->with('success', __('ui.content_duplicated') ?: 'Content duplicated. You can now edit and activate it.');
    }

    /**
     * Admin: Reorder items.
     */
    public function reorder(Request $request)
    {
        $tenantId = app(TenantManager::class)->getTenantId();
        $orders = $request->input('orders', []);

        foreach ($orders as $id => $order) {
            DisplayContent::where('id', $id)
                ->where('company_id', $tenantId)
                ->update(['sort_order' => (int) $order]);
        }

        return response()->json(['success' => true, 'message' => 'Display content reordered successfully.']);
    }

    /**
     * Admin: Preview content card matching Queue Display signage.
     */
    public function preview(DisplayContent $content)
    {
        $tenantId = app(TenantManager::class)->getTenantId();
        if ($content->company_id !== $tenantId) {
            abort(403);
        }

        $company = $content->company;

        return view('displays.contents.preview_card', compact('content', 'company'));
    }
}
