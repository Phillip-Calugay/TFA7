"""Capture real TFA7 browser workflows. Requires Python + playwright and Edge.

Run against a seeded local server; creates one customer and one user through forms.
Install tooling: python -m pip install playwright
Run: python scripts/capture_screenshots.py
"""
from datetime import datetime
from pathlib import Path
import json
from playwright.sync_api import sync_playwright, expect

ROOT = Path(__file__).resolve().parents[1]
OUT = ROOT / 'docs' / 'screenshots'
OUT.mkdir(parents=True, exist_ok=True)
BASE = 'http://127.0.0.1:8000'
suffix = datetime.now().strftime('%H%M%S')
email = f'tfa7.customer.{suffix}@example.com'
username = f'tfa7staff{suffix}'
manifest = []

with sync_playwright() as pw:
    browser = pw.chromium.launch(channel='msedge', headless=True)
    page = browser.new_page(viewport={'width': 1366, 'height': 1000}, device_scale_factor=1)
    page_errors = []
    page.on('pageerror', lambda error: page_errors.append(str(error)))

    def shot(filename, description):
        page.screenshot(path=str(OUT / filename), full_page=True, animations='disabled')
        manifest.append({'file': filename, 'description': description, 'url': page.url})

    page.goto(BASE + '/customers/create')
    page.locator('#full_name').fill('TFA7 Demo Customer')
    page.locator('#email').fill('invalid-email')
    page.locator('#phone').fill('0917 555 0707')
    page.get_by_role('button', name='Create customer').click()
    expect(page.locator('#email-error')).to_be_visible()
    expect(page.locator('#full_name')).to_have_value('TFA7 Demo Customer')
    shot('task-1-customer-validation.png', 'Invalid email rejected; entered name and phone retained.')
    page.locator('#email').fill(email)
    page.get_by_role('button', name='Create customer').click()
    expect(page.get_by_role('status')).to_have_text('Customer created successfully.')
    shot('task-1-customer-created.png', 'Valid customer inserted and displayed on the listing.')
    customer_edit = page.get_by_role('link', name='Edit TFA7 Demo Customer', exact=True).get_attribute('href')

    page.goto(BASE + '/users/create')
    page.locator('#username').fill('admin')
    page.get_by_role('button', name='Create user').click()
    expect(page.locator('#username-error')).to_be_visible()
    expect(page.locator('#full_name-error')).to_be_visible()
    shot('task-2-user-validation.png', 'Duplicate username and missing full name rejected; username retained.')
    page.locator('#username').fill(username)
    page.locator('#full_name').fill('TFA7 Demo Staff')
    page.locator('#role').select_option('Cashier')
    page.get_by_role('button', name='Create user').click()
    expect(page.get_by_role('status')).to_have_text('User created successfully.')
    shot('task-2-user-created.png', 'New unique staff account inserted.')
    user_edit = page.get_by_role('link', name='Edit TFA7 Demo Staff', exact=True).get_attribute('href')

    page.goto(customer_edit)
    expect(page.locator('#email')).to_have_value(email)
    shot('task-3-customer-edit-prefilled.png', 'Customer edit form pre-filled from the database.')
    page.locator('#full_name').fill('TFA7 Customer Updated')
    page.locator('#phone').fill('0999 777 0707')
    page.get_by_role('button', name='Save changes').click()
    expect(page.get_by_role('status')).to_have_text('Customer updated successfully.')
    expect(page.get_by_role('cell', name='TFA7 Customer Updated', exact=True)).to_be_visible()
    shot('task-3-customer-updated.png', 'Changed customer name and phone saved and displayed.')

    page.goto(user_edit)
    expect(page.locator('#username')).to_have_value(username)
    shot('task-3-user-edit-prefilled.png', 'User edit form pre-filled with existing name, username and role.')
    page.locator('#full_name').fill('TFA7 Staff Updated')
    page.locator('#role').select_option('Manager')
    page.get_by_role('button', name='Save changes').click()
    expect(page.get_by_role('status')).to_have_text('User updated successfully.')
    shot('task-3-user-updated.png', 'User name and role updated while retaining the same username.')

    page.goto(user_edit)
    page.locator('#avatar').set_input_files({'name': 'not-an-image.txt', 'mimeType': 'text/plain', 'buffer': b'TFA7 invalid upload'})
    page.get_by_role('button', name='Save changes').click()
    expect(page.locator('#avatar-error')).to_be_visible()
    shot('task-4-invalid-image-rejected.png', 'A text file is rejected by server image validation.')
    image = (ROOT / 'docs/fixtures/demo-avatar.png').read_bytes()
    page.locator('#avatar').set_input_files({'name': 'oversized.png', 'mimeType': 'image/png', 'buffer': image + b'\0' * (2049 * 1024)})
    page.get_by_role('button', name='Save changes').click()
    expect(page.locator('#avatar-error')).to_contain_text('2 MB')
    shot('task-4-oversized-image-rejected.png', 'An image larger than 2 MB is rejected.')
    page.locator('#avatar').set_input_files(str(ROOT / 'docs/fixtures/demo-avatar.png'))
    shot('task-4-avatar-selected.png', '900 by 600 source image selected in the multipart edit form.')
    page.get_by_role('button', name='Save changes').click()
    expect(page.get_by_role('status')).to_have_text('User updated successfully.')
    page.goto(user_edit)
    avatar = page.locator('img.preview')
    expect(avatar).to_be_visible()
    assert avatar.evaluate('(img) => img.complete && img.naturalWidth === 256 && img.naturalHeight === 256')
    shot('task-4-avatar-prepared.png', 'Uploaded image stored and served as an actual 256 by 256 JPEG thumbnail.')

    page.goto(BASE + '/users')
    images = page.locator('img.avatar')
    for img in images.all():
        assert img.evaluate('(img) => img.complete && img.naturalWidth > 0')
    expect(page.locator('img[src*="/storage/avatars/"]')).to_have_count(1)
    assert page.locator('img[src*="avatar-placeholder.svg"]').count() >= 5
    shot('task-5-user-avatars-and-placeholders.png', 'Listing shows the prepared uploaded avatar and placeholders for users without uploads.')
    page.set_viewport_size({'width': 390, 'height': 844})
    page.goto(user_edit)
    assert page.evaluate('document.documentElement.scrollWidth <= window.innerWidth')
    shot('mobile-user-edit.png', 'Responsive edit form at a mobile viewport.')
    assert not page_errors, page_errors
    (OUT / 'manifest.json').write_text(json.dumps(manifest, indent=2) + '\n', encoding='utf-8')
    browser.close()
print(f'Captured {len(manifest)} screenshots; all browser assertions passed.')
