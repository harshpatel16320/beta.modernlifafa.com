=== Harsh GitHub Sync ===
Tags: github, git, developer tools, deployment, version control
Requires at least: 6.0
Requires PHP: 7.4
Stable tag: 1.0
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Sync selected WordPress files with GitHub using HTTPS or SSH. Preview changes, manage branches and review pull requests from WordPress admin.

== Description ==

Harsh GitHub Sync is a developer tool by Harsh Patel for synchronizing selected WordPress files with a GitHub.com repository.

* Multiple repository profiles with encrypted saved HTTPS tokens and SSH key management.
* Whole-site or selected-file upload, editable exclusions and staged-file filters.
* Preview file additions, modifications and deletions before pushing.
* Large-file review, configurable size limits and optional Git LFS support.
* Create empty branches, copy a branch or copy another repository.
* Compare branches, find/create/review pull requests and merge reviewed code.
* Supported text-conflict editor, administrator controls and role-based permissions.
* Scheduled uploads, date-named branches and live process status.
* Database history, file manifests, duration/counts, filters and CSV exports.
* Retention controls and archive-before-delete/reset tools.

See FEATURES.txt for the detailed feature list and limitations.

This is a source-file synchronization tool, not a WordPress database backup or live-site deployment tool. Fetch and pull-request merges do not replace the running site's files. Configuration files may contain secrets; use a private repository and review exclusions carefully.

= Hosting requirements =

Linux/macOS PHP hosts are supported. Native Git synchronization needs Git, proc_open and SSH for SSH connections, available to the PHP/web-server account. PHP Sodium or OpenSSL generates SSH keys without ssh-keygen. OpenSSL is required for encrypted token storage. Large files may require installed git-lfs and GitHub LFS quota. Server/network execution limits can still apply.

= External service: GitHub =

After you configure a repository and request an operation, this plugin contacts github.com and api.github.com to authenticate, read repository data, upload selected files/commits, manage branches and work with pull requests. Scheduled uploads contact GitHub when explicitly enabled and armed by a successful manual push.

Data sent can include repository/branch names, authentication credentials, selected file contents, commit metadata, pull-request titles/descriptions and merge/conflict resolutions. Git LFS may contact storage endpoints configured by GitHub. GitHub account access and appropriate permissions are required; SSH deploy keys alone cannot authenticate pull-request API calls.

GitHub Terms: https://docs.github.com/en/site-policy/github-terms/github-terms-of-service
GitHub Privacy: https://docs.github.com/en/site-policy/privacy-policies/github-general-privacy-statement

The plugin adds no analytics or advertising and performs no GitHub requests merely on activation. Open Sans is bundled locally under SIL Open Font License 1.1; its license is in assets/fonts/OFL.txt.

= Stored data and removal =

Profiles, encrypted tokens, settings and history are stored in the WordPress database. Private SSH keys and isolated Git workspaces are stored outside the WordPress root in a private temporary directory. If that directory is cleared, generate/register a new deploy key.

History deletion and full reset create CSV audit archives before deleting data. Archives can remain in the plugin log directory, the wp-content/hpg-sync-private-log fallback or a temporary directory. Restrict direct HTTP access to archive directories, especially when using Nginx. Archives may contain repository and file-path information.

Deactivation and uninstall remove scheduled jobs and setup transients while preserving profiles/history for reinstall. To remove stored credentials/history/workspaces, use Settings > Archive history & fully reset before uninstalling. CSV archives and GitHub data are retained; remove archives separately when no longer needed.

== Installation ==

1. Upload the harsh-github-sync folder to /wp-content/plugins/, or upload the ZIP through Plugins > Add New.
2. Activate Harsh GitHub Sync.
3. Open GitHub Sync > Profile Settings and add your GitHub repository.
4. Add a repository-scoped HTTPS token, or generate an SSH key and register its public key as a deploy key.
5. Test the connection, choose source files/exclusions and preview changes before pushing.

== Frequently Asked Questions ==

= Does it work on localhost? =
Yes, on a supported PHP host with network access and the required Git/SSH tools.

= Can I use SSH without a token? =
Native Git operations can use SSH. GitHub pull-request tools and fast API branch creation require a saved HTTPS token with appropriate repository permissions.

= Does an empty branch merge into main? =
An empty branch has an independent initial commit. GitHub may refuse pull requests without shared history. Copy the destination branch when you intend to propose changes to it.

= Are cross-repository LFS files copied? =
Git history is copied, but LFS objects are not migrated. Migrate LFS storage separately.

= What permissions does a token need? =
Contents read/write for uploads and branch operations; Pull requests read/write for request tools. Workflow-file changes may require Workflows write access.

= Is uninstall destructive? =
No. Data is retained for reinstall; use the explicit archive/reset tool to remove plugin data before uninstalling.

== Changelog ==

= 1.0 =
* Initial submission package with profiles, Git sync, branch management, pull-request review, schedules and history.

== Upgrade Notice ==

= 1.0 =
Review hosting requirements and preview selected files before enabling scheduled uploads.
