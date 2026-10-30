---
paths:
  - 'app/Actions/**'
---

# Actions

## Business actions and boundaries
Put focused business operations in action classes with explicit typed contracts. Actions own business invariants, transactional mutations, errors, and external-call boundaries; reuse an action across HTTP, webhook jobs, and reconciliation for the same operation. Never call Stripe while holding inventory database locks.
