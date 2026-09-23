const fs = require('fs');
const path = require('path');

function isCanonicalRef(value) {
  return typeof value === 'string' && value.length > 0 && value.trim() === value;
}

function encodeCanonicalRef(value) {
  let encoded = "";
  for (const byte of Buffer.from(value, "utf8")) {
    const unreserved =
      (byte >= 0x41 && byte <= 0x5a) ||
      (byte >= 0x61 && byte <= 0x7a) ||
      (byte >= 0x30 && byte <= 0x39) ||
      byte === 0x2d || byte === 0x2e || byte === 0x5f || byte === 0x7e;
    encoded += unreserved ? String.fromCharCode(byte) : `%${byte.toString(16).toUpperCase().padStart(2, "0")}`;
  }
  return encoded;
}

const REF_FIELDS = ["surface", "boundary", "subsystem", "attack_class"];

function canonicalCoverageId(refs) {
  return REF_FIELDS.map((field) => encodeCanonicalRef(refs[field])).join("::");
}

const rawUnits = [
  {
    surface: "routes/web.php#GET /display/setup/{uid?}",
    boundary: "app/Modules/Displays/Controllers/DisplayDeviceController.php#authorizeDevice",
    subsystem: "app/Modules/Displays",
    attack_class: "WEB-PROTOCOL-AND-AUTH.md#Session and transport security",
    surface_label: "Display Setup and Device Pairing Endpoint",
    boundary_label: "Device Authorization and Pairing Code Generation",
    subsystem_label: "Displays Module",
    attack_class_label: "Session and transport security",
    starting_paths: [
      "app/Modules/Displays/Controllers/DisplayDeviceController.php",
      "app/Modules/Displays/Models/DisplayDevice.php",
      "routes/web.php"
    ],
    ordinary_attack_class_block: "ATTACK-CLASSES.md#Access control",
    selected_companion_blocks: [
      "WEB-PROTOCOL-AND-AUTH.md#Core discipline",
      "WEB-PROTOCOL-AND-AUTH.md#Session and transport security"
    ],
    excluded_blocks: [
      { block: "AI-AND-LLM.md#Prompt and context integrity", reason: "Target has no LLM prompt injection surface" }
    ]
  },
  {
    surface: "routes/web.php#POST /display/device/{token}/takeover",
    boundary: "app/Modules/Displays/Controllers/DisplayDeviceController.php#takeOverSession",
    subsystem: "app/Modules/Displays",
    attack_class: "ATTACK-CLASSES.md#Access control",
    surface_label: "Display Device Session Takeover Endpoint",
    boundary_label: "Session Takeover and Active Session Invalidation",
    subsystem_label: "Displays Module",
    attack_class_label: "Access control",
    starting_paths: [
      "app/Modules/Displays/Controllers/DisplayDeviceController.php",
      "app/Modules/Displays/Models/DisplayDevice.php",
      "routes/web.php"
    ],
    ordinary_attack_class_block: "ATTACK-CLASSES.md#Access control",
    selected_companion_blocks: [
      "WEB-PROTOCOL-AND-AUTH.md#Session and transport security"
    ],
    excluded_blocks: [
      { block: "AI-AND-LLM.md#Prompt and context integrity", reason: "Target has no LLM prompt injection surface" }
    ]
  },
  {
    surface: "routes/web.php#POST /customer/sync-ticket",
    boundary: "app/Modules/Customers/Controllers/CustomerPortalController.php#syncTicket",
    subsystem: "app/Modules/Customers",
    attack_class: "DATA-ISOLATION-AND-LIFECYCLE.md#Cross-tenant isolation",
    surface_label: "Customer Portal Ticket Sync",
    boundary_label: "Ticket Association to Customer Profile",
    subsystem_label: "Customers Module",
    attack_class_label: "Cross-tenant isolation",
    starting_paths: [
      "app/Modules/Customers/Controllers/CustomerPortalController.php",
      "app/Modules/Queue/Models/Ticket.php",
      "routes/web.php"
    ],
    ordinary_attack_class_block: "ATTACK-CLASSES.md#Access control",
    selected_companion_blocks: [
      "DATA-ISOLATION-AND-LIFECYCLE.md#Core discipline",
      "DATA-ISOLATION-AND-LIFECYCLE.md#Cross-tenant isolation"
    ],
    excluded_blocks: [
      { block: "AI-AND-LLM.md#Prompt and context integrity", reason: "Target has no LLM prompt injection surface" }
    ]
  },
  {
    surface: "routes/web.php#POST /customer/add-by-code",
    boundary: "app/Modules/Customers/Controllers/CustomerPortalController.php#addCompanyByCode",
    subsystem: "app/Modules/Customers",
    attack_class: "ATTACK-CLASSES.md#Access control",
    surface_label: "Customer Add Company By Code",
    boundary_label: "Company Code Resolution and Favorite Association",
    subsystem_label: "Customers Module",
    attack_class_label: "Access control",
    starting_paths: [
      "app/Modules/Customers/Controllers/CustomerPortalController.php",
      "app/Modules/Companies/Models/Company.php",
      "routes/web.php"
    ],
    ordinary_attack_class_block: "ATTACK-CLASSES.md#Access control",
    selected_companion_blocks: [
      "DATA-ISOLATION-AND-LIFECYCLE.md#Cross-tenant isolation"
    ],
    excluded_blocks: [
      { block: "AI-AND-LLM.md#Prompt and context integrity", reason: "Target has no LLM prompt injection surface" }
    ]
  },
  {
    surface: "routes/web.php#POST /simple/next",
    boundary: "app/Modules/Queue/Controllers/SimpleQueueController.php#callNext",
    subsystem: "app/Modules/Queue",
    attack_class: "DATA-ISOLATION-AND-LIFECYCLE.md#Cross-tenant isolation",
    surface_label: "Simple Queue Call Next Ticket",
    boundary_label: "Queue Ticket State Transition and Tenant Boundary",
    subsystem_label: "Queue Module",
    attack_class_label: "Cross-tenant isolation",
    starting_paths: [
      "app/Modules/Queue/Controllers/SimpleQueueController.php",
      "app/Modules/Queue/Models/Ticket.php",
      "routes/web.php"
    ],
    ordinary_attack_class_block: "ATTACK-CLASSES.md#Access control",
    selected_companion_blocks: [
      "DATA-ISOLATION-AND-LIFECYCLE.md#Cross-tenant isolation"
    ],
    excluded_blocks: [
      { block: "AI-AND-LLM.md#Prompt and context integrity", reason: "Target has no LLM prompt injection surface" }
    ]
  },
  {
    surface: "routes/web.php#PATCH /queue/{ticket}/status",
    boundary: "app/Modules/Queue/Controllers/TicketController.php#updateStatus",
    subsystem: "app/Modules/Queue",
    attack_class: "ATTACK-CLASSES.md#Access control",
    surface_label: "Queue Ticket Status Update Endpoint",
    boundary_label: "Ticket Authorization and Multi-Tenancy Scoping",
    subsystem_label: "Queue Module",
    attack_class_label: "Access control",
    starting_paths: [
      "app/Modules/Queue/Controllers/TicketController.php",
      "app/Modules/Queue/Models/Ticket.php",
      "routes/web.php"
    ],
    ordinary_attack_class_block: "ATTACK-CLASSES.md#Access control",
    selected_companion_blocks: [
      "DATA-ISOLATION-AND-LIFECYCLE.md#Cross-tenant isolation"
    ],
    excluded_blocks: [
      { block: "AI-AND-LLM.md#Prompt and context integrity", reason: "Target has no LLM prompt injection surface" }
    ]
  },
  {
    surface: "routes/web.php#GET /customers/ajax-search",
    boundary: "app/Modules/Customers/Controllers/CustomerController.php#ajaxSearch",
    subsystem: "app/Modules/Customers",
    attack_class: "DATA-ISOLATION-AND-LIFECYCLE.md#Cross-tenant isolation",
    surface_label: "Customer Ajax Search Endpoint",
    boundary_label: "Customer Search Query Scoping",
    subsystem_label: "Customers Module",
    attack_class_label: "Cross-tenant isolation",
    starting_paths: [
      "app/Modules/Customers/Controllers/CustomerController.php",
      "app/Modules/Customers/Models/Customer.php",
      "routes/web.php"
    ],
    ordinary_attack_class_block: "ATTACK-CLASSES.md#Information disclosure",
    selected_companion_blocks: [
      "DATA-ISOLATION-AND-LIFECYCLE.md#Cross-tenant isolation"
    ],
    excluded_blocks: [
      { block: "AI-AND-LLM.md#Prompt and context integrity", reason: "Target has no LLM prompt injection surface" }
    ]
  },
  {
    surface: "routes/web.php#POST /displays/contents",
    boundary: "app/Modules/Displays/Controllers/DisplayContentController.php#store",
    subsystem: "app/Modules/Displays",
    attack_class: "ATTACK-CLASSES.md#File and document processing",
    surface_label: "Display Digital Signage Media Upload",
    boundary_label: "Media Upload Validation and Storage",
    subsystem_label: "Displays Module",
    attack_class_label: "File and document processing",
    starting_paths: [
      "app/Modules/Displays/Controllers/DisplayContentController.php",
      "app/Modules/Displays/Models/DisplayContent.php",
      "routes/web.php"
    ],
    ordinary_attack_class_block: "ATTACK-CLASSES.md#File and document processing",
    selected_companion_blocks: [
      "WEB-PROTOCOL-AND-AUTH.md#Session and transport security"
    ],
    excluded_blocks: [
      { block: "AI-AND-LLM.md#Prompt and context integrity", reason: "Target has no LLM prompt injection surface" }
    ]
  },
  {
    surface: "routes/web.php#POST /billing/receipt",
    boundary: "app/Modules/Payments/Controllers/CompanyBillingController.php#uploadReceipt",
    subsystem: "app/Modules/Payments",
    attack_class: "ATTACK-CLASSES.md#File and document processing",
    surface_label: "Company Billing Payment Receipt Upload",
    boundary_label: "Receipt File Upload and MIME Validation",
    subsystem_label: "Payments Module",
    attack_class_label: "File and document processing",
    starting_paths: [
      "app/Modules/Payments/Controllers/CompanyBillingController.php",
      "app/Modules/Payments/Models/Payment.php",
      "routes/web.php"
    ],
    ordinary_attack_class_block: "ATTACK-CLASSES.md#File and document processing",
    selected_companion_blocks: [
      "WEB-PROTOCOL-AND-AUTH.md#Session and transport security"
    ],
    excluded_blocks: [
      { block: "AI-AND-LLM.md#Prompt and context integrity", reason: "Target has no LLM prompt injection surface" }
    ]
  },
  {
    surface: "routes/web.php#GET /analytics/export",
    boundary: "app/Modules/Reports/Controllers/AnalyticsController.php#exportCsv",
    subsystem: "app/Modules/Reports",
    attack_class: "ATTACK-CLASSES.md#Command, code, and query injection",
    surface_label: "Analytics CSV Report Export",
    boundary_label: "CSV Generation and Cell Value Sanitization",
    subsystem_label: "Reports Module",
    attack_class_label: "Command, code, and query injection",
    starting_paths: [
      "app/Modules/Reports/Controllers/AnalyticsController.php",
      "routes/web.php"
    ],
    ordinary_attack_class_block: "ATTACK-CLASSES.md#Command, code, and query injection",
    selected_companion_blocks: [
      "DATA-ISOLATION-AND-LIFECYCLE.md#Cross-tenant isolation"
    ],
    excluded_blocks: [
      { block: "AI-AND-LLM.md#Prompt and context integrity", reason: "Target has no LLM prompt injection surface" }
    ]
  },
  {
    surface: "routes/web.php#POST /webhook/whatsapp",
    boundary: "app/Modules/WhatsApp/Controllers/WhatsAppWebhookController.php#handle",
    subsystem: "app/Modules/WhatsApp",
    attack_class: "PROTOCOLS-RPC-AND-MESSAGING.md#Message authentication",
    surface_label: "WhatsApp Webhook Handler Endpoint",
    boundary_label: "Webhook Signature and Payload Verification",
    subsystem_label: "WhatsApp Module",
    attack_class_label: "Message authentication",
    starting_paths: [
      "app/Modules/WhatsApp/Controllers/WhatsAppWebhookController.php",
      "routes/web.php"
    ],
    ordinary_attack_class_block: "ATTACK-CLASSES.md#Access control",
    selected_companion_blocks: [
      "PROTOCOLS-RPC-AND-MESSAGING.md#Core discipline",
      "PROTOCOLS-RPC-AND-MESSAGING.md#Message authentication"
    ],
    excluded_blocks: [
      { block: "AI-AND-LLM.md#Prompt and context integrity", reason: "Target has no LLM prompt injection surface" }
    ]
  },
  {
    surface: "routes/web.php#PATCH /rbac/master/users/{user}/permissions",
    boundary: "app/Modules/RBAC/Controllers/MasterController.php#updateUserPermissions",
    subsystem: "app/Modules/RBAC",
    attack_class: "ATTACK-CLASSES.md#Access control",
    surface_label: "Master User Permissions Update Endpoint",
    boundary_label: "System Admin Authority Check and Permission Assignment",
    subsystem_label: "RBAC Module",
    attack_class_label: "Access control",
    starting_paths: [
      "app/Modules/RBAC/Controllers/MasterController.php",
      "app/Modules/RBAC/Middleware/SystemAdminMiddleware.php",
      "routes/web.php"
    ],
    ordinary_attack_class_block: "ATTACK-CLASSES.md#Access control",
    selected_companion_blocks: [
      "WEB-PROTOCOL-AND-AUTH.md#Session and transport security"
    ],
    excluded_blocks: [
      { block: "AI-AND-LLM.md#Prompt and context integrity", reason: "Target has no LLM prompt injection surface" }
    ]
  }
];

const ledgerUnits = rawUnits.map(unit => {
  const refs = {
    surface: unit.surface,
    boundary: unit.boundary,
    subsystem: unit.subsystem,
    attack_class: unit.attack_class
  };
  const coverage_id = canonicalCoverageId(refs);
  return {
    coverage_id: coverage_id,
    canonical_refs: refs,
    surface: unit.surface_label,
    boundary: unit.boundary_label,
    subsystem: unit.subsystem_label,
    attack_class: unit.attack_class_label,
    starting_paths: unit.starting_paths,
    ordinary_attack_class_block: unit.ordinary_attack_class_block,
    selected_companion_blocks: unit.selected_companion_blocks,
    excluded_blocks: unit.excluded_blocks,
    prior_status: "none",
    attempts: [],
    wave: 1,
    status: "planned",
    agent_id: null,
    reviewed_paths: [],
    local_checks: [],
    result_fingerprints: [],
    unresolved: []
  };
});

ledgerUnits.sort((a, b) => a.coverage_id.localeCompare(b.coverage_id));

const outPath = path.resolve(__dirname, '../security-audit-report/run-1/coverage-ledger.json');
fs.writeFileSync(outPath, JSON.stringify(ledgerUnits, null, 2), 'utf8');
console.log(`Seeded ${ledgerUnits.length} ledger units to ${outPath}`);
