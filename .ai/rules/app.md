---
paths:
  - 'app/**'
---

# App

## Application structure
Use standard Laravel directories grouped by feature (for example app/Http/Controllers/Checkout, app/Http/Requests/Checkout, app/Actions/Checkout, app/Data/Checkout), with shared app/Models and app/Values. Do not add a parallel app/Features architecture. Avoid speculative repositories, services, interfaces, or wrappers; new abstractions need approved justification.
