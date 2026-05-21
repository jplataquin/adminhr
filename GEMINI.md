# AdminHR Project Instructions

## Tech Stack
*   **Backend:** Laravel 13 (PHP 8.3+)
*   **Frontend:** Blade Templates, Bootstrap 5, Alpine.js
*   **Assets:** Vite (SASS)
*   **Testing:** Pest (PHP)
*   **Key Dependencies:** 
    *   `filepond` (Advanced image/file uploads and cropping)
    *   `simplesoftwareio/simple-qrcode` (QR code generation)
    *   `bootstrap` & `bootstrap-icons`
    *   `jszip` (ZIP file processing)

## Core Architecture & Domain
*   **Employee Management:** Handles employee records, profile images, and bulk processing. Includes printable template views for generating physical ID cards (e.g., `employee-template-id.blade.php`).
*   **Ledger System:** Tracks organizational records via `Ledgers`, `LedgerAccounts`, and `LedgerEntries`. 
*   **Review & Approval Workflow:** An advanced maker-checker approval system baked into the ledger and general system components. Actions such as modifications or deletions of ledger entries/accounts require multi-stage authorization (utilizing metadata fields like `request_delete_by`, `approve_by`, and `rejected_by`).
*   **Authentication:** Powered by Laravel Breeze (Blade/Alpine version) with standard user profiles and session management.

## Coding Conventions
*   **Testing:** Write all tests using Pest in the `tests/` directory.
*   **Views & UI:** Heavily rely on Laravel Blade components (both class-based in `app/View/Components/` and anonymous in `resources/views/components/`). Use Bootstrap 5 for all styling.
*   **JavaScript:** Use Alpine.js for lightweight declarative reactivity within Blade templates. Keep heavier client-side logic organized inside `resources/js/`.
