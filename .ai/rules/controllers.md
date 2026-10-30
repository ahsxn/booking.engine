---
paths:
  - 'app/Http/Controllers/**'
---

# Controllers

## Thin invokable controllers
Use one invokable __invoke controller per HTTP endpoint. Keep controllers thin: accept validated input, call an action, then return an HTTP response or Inertia page. Do not put business transactions in controllers.
