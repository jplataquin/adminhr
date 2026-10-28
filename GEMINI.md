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
*   **Testing:** Write all tests using Pest in the `tests/` directory. Only run targeted tests for the specific code, feature, or area being modified (e.g. `php artisan test tests/Feature/...` or `vendor/bin/pest tests/Feature/...`) to save development time; do not run the entire test suite on every change.
*   **Views & UI:** Heavily rely on Laravel Blade components (both class-based in `app/View/Components/` and anonymous in `resources/views/components/`). Use Bootstrap 5 for all styling.
*   **JavaScript:** Use Alpine.js for lightweight declarative reactivity within Blade templates. Keep heavier client-side logic organized inside `resources/js/`.

## Parallel Agent Coordination
You are operating in a multi-agent environment where multiple Gemini sessions run in parallel git worktrees created via `gemini -w <name>`. To prevent merge conflicts and optimize work across concurrent agents, follow this coordination and shipping protocol:

1. **Self-Identification & Setup**:
   - Determine your agent ID from your workspace directory name (e.g., if working in `.gemini/worktrees/task-1`, your ID is `task-1`).
   - Automatically ensure the shared state folder is symlinked by running:
     `mkdir -p ../../.agent-states && ln -sfn ../../.agent-states .agent-states`
   - Your dedicated state file is `.agent-states/<your_agent_id>.md`.

2. **Heartbeat & Broadcasting**:
   - As soon as you define your plan or change intent, write/update your state file with:
     ```markdown
     # Agent: <your_agent_id>
     - **Status**: Active
     - **Updated At**: YYYY-MM-DD HH:MM UTC
     - **Current Intent / Task**: <brief description of what you are doing>
     - **Components Modified**:
       - <file or component 1>
       - <file or component 2>
     ```

3. **Dynamic Conflict Checking**:
   - Before executing code edits (`replace`, `write_file`, etc.), inspect all other files in `.agent-states/`.
   - Ignore your own state file.
   - Ignore state files with `Status: Idle` or with an `Updated At` older than 30 minutes (stale).
   - If an active agent lists overlapping files or components in `Components Modified`, adjust your plan, wait, or ask the user before proceeding.

4. **Automated Completion & Shipping Workflow (Ship to Main)**:
   Once your task is completed and verified, automatically ship your changes to `main`:
   a. **Descriptive Commit**:
      - Stage changed files: `git add <files>` (avoid committing untracked environment or temporary files).
      - Commit with a clear, descriptive message: `git commit -m "<type>(<scope>): <concise description of work>"`.
   b. **Sync with Main**:
      - Fetch latest remote changes: `git fetch origin main`
      - Merge main into your branch: `git merge origin/main`
   c. **Reconcile Conflicts (if any)**:
      - If conflict markers exist (`<<<<<<<`, `=======`, `>>>>>>>`), inspect and intelligently merge the code so neither your changes nor incoming changes are lost.
      - Perform syntax checks on touched files (e.g., `php -l <file>`).
      - Stage and commit the merge: `git commit -m "chore: merge origin/main and resolve conflicts"`
   d. **Direct Push to Main**:
      - Push your branch directly to the remote main branch: `git -c credential.helper=store push origin HEAD:main`
   e. **Clean State**:
      - Mark your state file `.agent-states/<your_agent_id>.md` as `Status: Idle` or remove it.
      - Announce: `🚀 Task successfully combined and shipped directly to main!`

