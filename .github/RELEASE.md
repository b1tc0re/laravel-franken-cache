# Release process

Releases are managed automatically with
[Release Please](https://github.com/googleapis/release-please).

## Workflow

```text
develop → pull request → main → Release PR → merge → vX.Y.Z + GitHub Release
```

After changes from `develop` are merged into `main`, the Release Please
workflow creates or updates a release pull request with the next version and
the changelog. Merging that release pull request creates the `vX.Y.Z` tag and
the GitHub release.

## Commit messages

Use Conventional Commits so the next version can be determined automatically:

- `fix:` creates a patch release;
- `feat:` creates a minor release;
- `feat!:` or another breaking change creates a major release.

## GitHub setup

The repository must allow GitHub Actions to create and approve pull requests:

`Settings → Actions → General → Allow GitHub Actions to create and approve pull requests`

The workflow uses the built-in `GITHUB_TOKEN` by default. Add a repository
secret named `RELEASE_PLEASE_TOKEN` if checks must run on pull requests created
by Release Please.

Packagist should have a GitHub webhook configured so it receives new release
tags automatically.
