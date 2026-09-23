@props(['text' => '', 'placement' => 'top'])

<span class="d-inline-flex align-items-center ms-1 text-muted hover-primary cursor-pointer" 
      data-bs-toggle="tooltip" 
      data-bs-placement="{{ $placement }}" 
      title="{{ $text }}" 
      style="font-size: 0.85rem;">
    <i class="bi bi-info-circle-fill opacity-75"></i>
</span>
