# simplesamlphp-typo3auth
Typo3 authentication module for SimpleSAMLphp

## What does it do?

Authenticate using Typo3 **frontend** users from the `fe_users` table.

## How to install?

1. Copy the folder _typo3auth_ from the _modules_ directory to the _modules_ directory of your SimpleSAMLphp instance.
2. Configure the database access like shown in _config/authsources.php_.
3. Enable the module like shown in _config/config.php_.

## Known issues

- Currently only frontend users can be used, as I had no need for backend users. Should be easy to add tho, if you want me to implement it, just open an issue.
- Tested with Typo3 v11 and v12, other versions might work as well but I can't be sure.