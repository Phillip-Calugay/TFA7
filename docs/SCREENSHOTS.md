# TFA7 numbered task outputs

These screenshots show real browser form submissions at http://127.0.0.1:8000. The [source image](fixtures/demo-avatar.png) is a 900×600 PNG; its prepared result was verified as a 256×256 JPEG.

## 1. Create Customer

Invalid email produces a field error and preserves the entered name and phone.

![Task 1 validation and old input](screenshots/task-1-customer-validation.png)

Correcting the email inserts the customer and redirects to the listing with confirmation.

![Task 1 created customer](screenshots/task-1-customer-created.png)

## 2. Create User

A duplicate username and blank full name are rejected.

![Task 2 validation](screenshots/task-2-user-validation.png)

A unique username and valid details create a staff record.

![Task 2 created user](screenshots/task-2-user-created.png)

## 3. Edit Customer and User

The customer form contains existing database values.

![Task 3 customer edit](screenshots/task-3-customer-edit-prefilled.png)

The updated name and phone appear after saving.

![Task 3 saved customer](screenshots/task-3-customer-updated.png)

The user form contains the existing username, name and selected role.

![Task 3 user edit](screenshots/task-3-user-edit-prefilled.png)

The new name and role appear after saving, retaining the same username.

![Task 3 saved user](screenshots/task-3-user-updated.png)

## 4. Validate, prepare and store an avatar

A text file is rejected as an image upload.

![Task 4 invalid image](screenshots/task-4-invalid-image-rejected.png)

An image larger than 2 MB is rejected without saving account changes.

![Task 4 size validation](screenshots/task-4-oversized-image-rejected.png)

A valid source image is selected. File selection alone does not save it; Save changes submits the form.

![Task 4 selected image](screenshots/task-4-avatar-selected.png)

After saving, the edit page loads the prepared thumbnail from the public disk. The browser verified natural dimensions of 256×256; the automated test independently verifies JPEG format and the relative stored path.

![Task 4 prepared image](screenshots/task-4-avatar-prepared.png)

## 5. Display uploaded avatar and fallback images

The last row uses its uploaded thumbnail. Seeded users without an upload show the placeholder. Every image was checked for successful loading.

![Task 5 avatars and placeholders](screenshots/task-5-user-avatars-and-placeholders.png)

## Additional mobile check

The edit form fits a 390-pixel viewport without page-level horizontal overflow.

![Mobile edit form](screenshots/mobile-user-edit.png)
