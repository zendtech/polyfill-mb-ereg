# CONTRIBUTING

## RESOURCES

If you wish to contribute to this project, please be sure to read/subscribe to the following resources:

- [Coding Standards](https://github.com/laminas/laminas-coding-standard)
- [Code of Conduct](CODE_OF_CONDUCT.md)

If you are working on new features or refactoring, [create a proposal](./issues/new?labels=RFC&template=RFC.md&title=%5BRFC%5D%3A+%5BTITLE+OF+RFC%5D).

## RUNNING TESTS

To run tests:

- Clone the repository.
  Click the "Clone or download" button on the repository to find both the URL and instructions on cloning.

- Install dependencies via composer:

  ```console
  $ composer install
  ```

  If you don't have `composer` installed, please download it from https://getcomposer.org/download/

- Run the tests using the "test" command shipped in the `composer.json`:

  ```console
  $ composer test
  ```

You can turn on conditional tests with the `phpunit.xml` file.  To do so:

- Copy the `phpunit.xml.dist` file to `phpunit.xml`
- Edit the `phpunit.xml` file to enable any specific functionality you want to test, as well as to provide test values to utilize.

## Running Coding Standards Checks

First, ensure you've installed dependencies via composer, per the previous section on running tests.

To run CS checks only:

```console
$ composer cs-check
```

To attempt to automatically fix common CS issues:

```console
$ composer cs-fix
```

If the above fixes any CS issues, please re-run the tests to ensure they pass, and make sure you add and commit the changes after verification.

## Recommended Workflow for Contributions

Your first step is to establish a public repository from which we can pull your work into the canonical repository.
We recommend using [GitHub](https://github.com), as that is where the component is already hosted.

1. Setup a [GitHub account](https://github.com/join), if you haven't yet
2. Fork the repository using the "Fork" button at the top right of the repository landing page.
3. Clone the canonical repository locally.
   Use the "Clone or download" button above the code listing on the repository landing pages to obtain the URL and instructions.
4. Navigate to the directory where you have cloned the repository.
5. Add a remote to your fork; substitute your GitHub username and the repository name in the commands below.

   ```console
   $ git remote add fork git@github.com:{username}/{repository}.git
   $ git fetch fork
   ```

Alternately, you can use the [GitHub CLI tool](https://cli.github.com) to accomplish these steps:

```console
$ gh repo clone {org}/{repo}
$ cd {repo}
$ gh repo fork
```

### Keeping Up-to-Date

Periodically, you should update your fork or personal repository to match the canonical repository.
Assuming you have setup your local repository per the instructions above, you can do the following:


```console
$ git fetch origin
$ git switch {branch to update}
$ git pull --rebase --autostash
# OPTIONALLY, to keep your remote up-to-date -
$ git push fork {branch}:{branch}
```

If you're tracking other release branches, you'll want to do the same operations for each branch.

### Working on a patch

We recommend you do each new feature or bugfix in a new branch.
This simplifies the task of code review as well as the task of merging your changes into the canonical repository.

A typical workflow will then consist of the following:

1. Create a new local branch based off the appropriate release branch.
2. Switch to your new local branch.
   (This step can be combined with the previous step with the use of `git switch -c {new branch} {original branch}`, or, if the original branch is the current one, `git switch -c {new branch}`.)
3. Do some work, commit, repeat as necessary.
4. Push the local branch to your remote repository.
5. Send a pull request.

The mechanics of this process are actually quite trivial. Below, we will
create a branch for fixing an issue in the tracker.

```console
$ git switch -c hotfix/9295
Switched to a new branch 'hotfix/9295'
```

... do some work ...


```console
$ git commit -s
```

> ### About the -s flag
>
> See the [section on commit signoffs](#commit-signoffs) below for more details on the `-s` option to `git commit` and why we require it.

... write your log message ...

```console
$ git push fork hotfix/9:hotfix/9
Counting objects: 38, done.
Delta compression using up to 2 threads.
Compression objects: 100% (18/18), done.
Writing objects: 100% (20/20), 8.19KiB, done.
Total 20 (delta 12), reused 0 (delta 0)
To ssh://git@github.com/{username}/polyfill-mb-ereg.git
   b5583aa..4f51698  HEAD -> hotfix/9
```

To send a pull request, you have several options.

If using GitHub, you can do the pull request from there.
Navigate to your repository, select the branch you just created, and then select the "Pull Request" button in the upper right.
Select the user/organization "laminas" (or whatever the upstream organization is) as the recipient.

You can also perform the same steps via the [GitHub CLI tool](https://cli.github.com).
Execute `gh pr create`, and step through the dialog to create the pull request.
If the branch you will submit against is not the default branch, use the `-B {branch}` option to specify the branch to create the patch against.

### Branch Cleanup

As you might imagine, if you are a frequent contributor, you'll start to get a ton of branches both locally and on your remote.

Once you know that your changes have been accepted to the canonical repository, we suggest doing some cleanup of these branches.

- Local branch cleanup

  ```console
  $ git branch -d <branchname>
  ```

- Remote branch removal

  ```console
  $ git push fork :<branchname>
  ```
