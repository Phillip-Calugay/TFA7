"""Build the task screenshot gallery from the actual browser capture manifest."""
import json
from pathlib import Path

ROOT = Path(__file__).resolve().parents[1]
manifest = json.loads((ROOT / 'docs/screenshots/tfa8/manifest.json').read_text(encoding='utf-8'))
titles = {
    1: 'Continue the Laravel POS from TFA5–TFA7',
    2: 'Customer resource routes and controller',
    3: 'User resource routes and controller',
    4: 'Install and configure Laravel Breeze',
    5: 'Protect account routes with auth middleware',
    6: 'Verify logged-out and logged-in access',
}
lines = ['# TFA8 task screenshots', '',
         'Real Microsoft Edge browser captures of **http://127.0.0.1:8000**. Local hosting only.', '',
         'The [capture manifest](screenshots/tfa8/manifest.json) records the route, task, description and UTC capture time for every image. '
         'The [capture script](../scripts/capture_tfa8.py) asserts redirects, form results and avatar dimensions before saving evidence.', '',
         'Tasks 5 and 6 show the login page after a protected request. The script checks the actual redirect chain; '
         'automated tests additionally verify that every resource action redirects guests.', '']
for task, title in titles.items():
    lines += [f'## {task}. {title}', '']
    for item in manifest['images']:
        if item['task'] == task:
            lines += [item['description'], '', f"![Task {task}: {item['description']}](screenshots/tfa8/{item['file']})", '']
(ROOT / 'docs/TFA8-SCREENSHOTS.md').write_text('\n'.join(lines), encoding='utf-8')
print(f"Gallery written with {len(manifest['images'])} screenshots across {len(titles)} tasks.")
