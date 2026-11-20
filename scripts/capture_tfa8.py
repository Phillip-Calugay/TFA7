"""Capture TFA8 tasks against localhost. Requires Playwright and Microsoft Edge.

Creates a demo login through registration and temporary CRUD records via forms.
The password is read from TFA8_DEMO_PASSWORD and is never included in artifacts.
"""
from datetime import datetime, timezone
from pathlib import Path
import json
import os
import sys

ROOT = Path(__file__).resolve().parents[1]
sys.path.insert(0, str(ROOT / 'storage/tools'))
from playwright.sync_api import sync_playwright, expect

OUT = ROOT / 'docs/screenshots/tfa8'
OUT.mkdir(parents=True, exist_ok=True)
BASE = 'http://127.0.0.1:8000'
suffix = datetime.now().strftime('%Y%m%d%H%M%S')
email = f'tfa8.{suffix}@example.com'
password = os.environ['TFA8_DEMO_PASSWORD']
manifest = []

with sync_playwright() as pw:
    browser = pw.chromium.launch(channel='msedge', headless=True)
    context = browser.new_context(viewport={'width': 1440, 'height': 1050}, device_scale_factor=1)
    page = context.new_page()
    errors = []
    page.on('pageerror', lambda error: errors.append(str(error)))

    def shot(name, task, description):
        page.evaluate('window.scrollTo(0, 0)')
        page.wait_for_timeout(100)
        page.screenshot(path=str(OUT / name), full_page=True, animations='disabled')
        manifest.append({'file': name, 'task': task, 'description': description, 'url': page.url,
                         'captured_at': datetime.now(timezone.utc).isoformat()})

    page.goto(BASE)
    expect(page.get_by_role('heading', name='Welcome', exact=True)).to_be_visible()
    shot('task-1-existing-pos.png', 1, 'Continued Laravel POS with Home, About, Customers and Users navigation.')

    for resource in ['customers', 'users']:
        response = page.goto(BASE + '/' + resource)
        assert response.status == 200 and page.url == BASE + '/login'
        chain = response.request.redirected_from
        assert chain and chain.url == BASE + '/' + resource
        shot(f'task-5-{resource}-requires-login.png', 5, f'Guest request to /{resource} redirected to the Breeze login page.')

    page.goto(BASE + '/register')
    shot('task-4-registration.png', 4, 'Breeze registration with full name, username, email, password and confirmation.')
    page.locator('#full_name').fill('TFA8 Demo Operator')
    page.locator('#username').fill('tfa8operator' + suffix)
    page.locator('#email').fill(email)
    page.locator('#password').fill(password)
    page.locator('#password_confirmation').fill(password)
    page.get_by_role('button', name='Register', exact=True).click()
    expect(page).to_have_url(BASE + '/dashboard')
    shot('task-4-registered-dashboard.png', 4, 'Successful registration signs in the new Staff account.')

    page.goto(BASE + '/customers')
    expect(page.get_by_role('heading', name='Customer Accounts', exact=True)).to_be_visible()
    shot('task-2-customer-resource-index.png', 2, 'Authenticated resource index retains existing customer records.')
    page.goto(BASE + '/customers/create')
    page.locator('#full_name').fill('TFA8 Resource Customer')
    page.locator('#email').fill(f'customer.{suffix}@example.com')
    page.locator('#phone').fill('0917 888 0808')
    shot('task-2-customer-create.png', 2, 'Resource create form with CSRF token.')
    page.get_by_role('button', name='Create customer', exact=True).click()
    expect(page.get_by_role('status')).to_have_text('Customer created successfully.')
    edit = page.get_by_role('link', name='Edit TFA8 Resource Customer', exact=True).get_attribute('href')
    page.goto(edit)
    page.locator('#full_name').fill('TFA8 Customer Updated')
    page.get_by_role('button', name='Save changes', exact=True).click()
    expect(page.get_by_role('status')).to_have_text('Customer updated successfully.')
    page.get_by_role('link', name='View TFA8 Customer Updated', exact=True).click()
    shot('task-2-customer-show-updated.png', 2, 'Resource show displays the saved update and DELETE form.')
    page.get_by_role('button', name='Delete customer', exact=True).click()
    expect(page.get_by_role('status')).to_have_text('Customer deleted successfully.')
    shot('task-2-customer-deleted.png', 2, 'Resource destroy deletes the temporary customer.')

    page.goto(BASE + '/users')
    shot('task-3-user-resource-index.png', 3, 'Authenticated users resource retains staff roles and avatar thumbnails.')
    page.goto(BASE + '/users/create')
    page.locator('#username').fill('tfa8staff' + suffix)
    page.locator('#full_name').fill('TFA8 Resource Staff')
    page.locator('#role').select_option('Cashier')
    shot('task-3-user-create.png', 3, 'Resource create form retains validated staff fields.')
    page.get_by_role('button', name='Create user', exact=True).click()
    expect(page.get_by_role('status')).to_have_text('User created successfully.')
    staff_row = page.get_by_role('row').filter(has=page.get_by_role('cell', name='tfa8staff' + suffix, exact=True))
    staff_row.get_by_role('link', name='Edit TFA8 Resource Staff', exact=True).click()
    page.locator('#full_name').fill('TFA8 Staff Updated')
    page.locator('#role').select_option('Manager')
    page.locator('#avatar').set_input_files(str(ROOT / 'docs/fixtures/demo-avatar.png'))
    page.get_by_role('button', name='Save changes', exact=True).click()
    expect(page.get_by_role('status')).to_have_text('User updated successfully.')
    row = page.get_by_role('row').filter(has=page.get_by_role('cell', name='TFA8 Staff Updated', exact=True))
    expect(row.locator('img')).to_be_visible()
    assert row.locator('img').evaluate('(img) => img.complete && img.naturalWidth === 256')
    shot('task-3-user-updated-avatar.png', 3, 'PUT update preserves role editing and prepared 256 pixel avatar upload.')
    page.get_by_role('link', name='View TFA8 Staff Updated', exact=True).click()
    shot('task-3-user-show.png', 3, 'Resource show displays staff details and delete action.')
    page.get_by_role('button', name='Delete user', exact=True).click()
    expect(page.get_by_role('status')).to_have_text('User deleted successfully.')
    shot('task-3-user-deleted.png', 3, 'Resource destroy deletes the temporary staff account and its avatar.')

    page.get_by_role('button', name='Log out', exact=True).click()
    page.goto(BASE + '/customers')
    expect(page).to_have_url(BASE + '/login')
    shot('task-6-logged-out-redirect.png', 6, 'After logout a protected customer request redirects to login.')
    page.locator('#email').fill(email)
    page.locator('#password').fill('incorrect-password')
    page.get_by_role('button', name='Log in', exact=True).click()
    expect(page.get_by_text('These credentials do not match our records.')).to_be_visible()
    shot('task-6-invalid-login.png', 6, 'Invalid password is rejected by Breeze.')
    page.locator('#password').fill(password)
    page.get_by_role('button', name='Log in', exact=True).click()
    expect(page).to_have_url(BASE + '/customers')
    shot('task-6-logged-in-customers.png', 6, 'Successful login returns to the original intended protected customer page.')
    page.goto(BASE + '/users')
    shot('task-6-logged-in-users.png', 6, 'The same session can access the protected users page normally.')
    page.set_viewport_size({'width': 390, 'height': 844})
    page.goto(BASE + '/profile')
    assert page.evaluate('document.documentElement.scrollWidth <= innerWidth')
    shot('mobile-profile.png', 6, 'Profile and authentication navigation remain usable on a narrow screen.')
    page.get_by_role('button', name='Log out', exact=True).click()
    assert not errors, errors
    browser.close()

(OUT / 'manifest.json').write_text(json.dumps({'base_url': BASE, 'images': manifest, 'browser_errors': errors}, indent=2), encoding='utf-8')
print(f'Captured {len(manifest)} screenshots covering tasks 1–6 at {BASE}.')
