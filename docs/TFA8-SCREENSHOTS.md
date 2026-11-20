# TFA8 task screenshots

Real Microsoft Edge browser captures of **http://127.0.0.1:8000**. Local hosting only.

The [capture manifest](screenshots/tfa8/manifest.json) records the route, task, description and UTC capture time for every image. The [capture script](../scripts/capture_tfa8.py) asserts redirects, form results and avatar dimensions before saving evidence.

Tasks 5 and 6 show the login page after a protected request. The script checks the actual redirect chain; automated tests additionally verify that every resource action redirects guests.

## 1. Continue the Laravel POS from TFA5–TFA7

Continued Laravel POS with Home, About, Customers and Users navigation.

![Task 1: Continued Laravel POS with Home, About, Customers and Users navigation.](screenshots/tfa8/task-1-existing-pos.png)

## 2. Customer resource routes and controller

Authenticated resource index retains existing customer records.

![Task 2: Authenticated resource index retains existing customer records.](screenshots/tfa8/task-2-customer-resource-index.png)

Resource create form with CSRF token.

![Task 2: Resource create form with CSRF token.](screenshots/tfa8/task-2-customer-create.png)

Resource show displays the saved update and DELETE form.

![Task 2: Resource show displays the saved update and DELETE form.](screenshots/tfa8/task-2-customer-show-updated.png)

Resource destroy deletes the temporary customer.

![Task 2: Resource destroy deletes the temporary customer.](screenshots/tfa8/task-2-customer-deleted.png)

## 3. User resource routes and controller

Authenticated users resource retains staff roles and avatar thumbnails.

![Task 3: Authenticated users resource retains staff roles and avatar thumbnails.](screenshots/tfa8/task-3-user-resource-index.png)

Resource create form retains validated staff fields.

![Task 3: Resource create form retains validated staff fields.](screenshots/tfa8/task-3-user-create.png)

PUT update preserves role editing and prepared 256 pixel avatar upload.

![Task 3: PUT update preserves role editing and prepared 256 pixel avatar upload.](screenshots/tfa8/task-3-user-updated-avatar.png)

Resource show displays staff details and delete action.

![Task 3: Resource show displays staff details and delete action.](screenshots/tfa8/task-3-user-show.png)

Resource destroy deletes the temporary staff account and its avatar.

![Task 3: Resource destroy deletes the temporary staff account and its avatar.](screenshots/tfa8/task-3-user-deleted.png)

## 4. Install and configure Laravel Breeze

Breeze registration with full name, username, email, password and confirmation.

![Task 4: Breeze registration with full name, username, email, password and confirmation.](screenshots/tfa8/task-4-registration.png)

Successful registration signs in the new Staff account.

![Task 4: Successful registration signs in the new Staff account.](screenshots/tfa8/task-4-registered-dashboard.png)

## 5. Protect account routes with auth middleware

Guest request to /customers redirected to the Breeze login page.

![Task 5: Guest request to /customers redirected to the Breeze login page.](screenshots/tfa8/task-5-customers-requires-login.png)

Guest request to /users redirected to the Breeze login page.

![Task 5: Guest request to /users redirected to the Breeze login page.](screenshots/tfa8/task-5-users-requires-login.png)

## 6. Verify logged-out and logged-in access

After logout a protected customer request redirects to login.

![Task 6: After logout a protected customer request redirects to login.](screenshots/tfa8/task-6-logged-out-redirect.png)

Invalid password is rejected by Breeze.

![Task 6: Invalid password is rejected by Breeze.](screenshots/tfa8/task-6-invalid-login.png)

Successful login returns to the original intended protected customer page.

![Task 6: Successful login returns to the original intended protected customer page.](screenshots/tfa8/task-6-logged-in-customers.png)

The same session can access the protected users page normally.

![Task 6: The same session can access the protected users page normally.](screenshots/tfa8/task-6-logged-in-users.png)

Profile and authentication navigation remain usable on a narrow screen.

![Task 6: Profile and authentication navigation remain usable on a narrow screen.](screenshots/tfa8/mobile-profile.png)
