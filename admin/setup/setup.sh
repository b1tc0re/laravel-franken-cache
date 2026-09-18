#!/bin/sh

# Install a pre-commit hook only inside a git checkout.
if [ ! -d .git/hooks ]; then
    exit 0
fi

cp admin/hooks/pre-commit .git/hooks/pre-commit
chmod +x .git/hooks/pre-commit
