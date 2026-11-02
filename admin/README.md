# Administration area

This directory contains the TCExam administration interface (the management back end, under
`admin/code/`).

**This directory must be protected.** Access must be allowed only to site administrators or
editors. The shipped `.htaccess` rules and the application's own authentication enforce this on
Apache; replicate equivalent access restrictions if you use a different web server.

Serve this directory as a separate, internal service (dedicated host, VPN or access gateway,
firewall, second authentication layer). See "Isolating the administration area" in
[SECURITY.md](../SECURITY.md).
