###  Summary
_Version OSneo: 1.X_
_Type: Hotfix, Patch_

### Details
_changelog_

### Testing
- [ ] unit test
- [ ] rework integration test
- [ ] Check translation
- [ ] integration test

### Deploy
- [ ] Change version number
- [ ] Rename folder (database/migrations/update) to version number
- [ ] sudo -u www php artisan down
- [ ] Run database backup
- [ ] sudo apt update && sudo apt upgrade
- [ ] sudo -u www git reset --hard
- [ ] sudo -u www git pull
- [ ] sudo -u www composer install
- [ ] sudo -u www npm install
- [ ] sudo -u www npm run build
- [ ] sudo -u www php artisan optimize:clear
- [ ] sudo -u www php artisan config:cache
- [ ] sudo -u www php artisan route:cache
- [ ] sudo -u www php artisan view:cache
- [ ] sudo -u www php artisan event:cache
- [ ] sudo -u www composer clear-cache
- [ ] sudo -u www composer dump-autoload
- [ ] sudo -u www npm cache clean --force
- [ ] sudo -u www php artisan migrate --path=/database/migrations/X.X
- [ ] sudo -u www rm public/storage
- [ ] sudo -u www php artisan storage:link
- [ ] sudo -u www php artisan up
- [ ] Integration test
- [ ] Employee information
- [ ] Merge master with dev and hotfix branch
