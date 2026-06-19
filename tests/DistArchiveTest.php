<?php

// Regression test for the Windows install failure (issue: "Cannot create symbolic link").
//
// The package shipped the `workbench/` dev scaffold, which contains the
// `workbench/storage` symlink. Composer's dist archive included it, and
// extracting that symlink on Windows fails without the privilege.
//
// These tests assert the dist archive (git archive, honouring
// `.gitattributes` export-ignore) ships neither the workbench scaffold nor
// any symlink. `--worktree-attributes` makes git honour the working-tree
// `.gitattributes`, so the test is valid even before the fix is committed.

function distArchiveEntries(): array
{
    $root = dirname(__DIR__);

    $output = shell_exec(
        'cd '.escapeshellarg($root).' && git archive --worktree-attributes HEAD 2>/dev/null | tar -t'
    );

    return array_values(array_filter(array_map('trim', explode("\n", (string) $output))));
}

it('produces a non-empty dist archive', function () {
    expect(distArchiveEntries())->not->toBeEmpty();
});

it('does not ship the workbench scaffold in the dist archive', function () {
    $workbench = array_filter(
        distArchiveEntries(),
        fn (string $path) => str_starts_with($path, 'workbench/'),
    );

    expect($workbench)->toBe([]);
});

it('does not ship any tracked symlink in the dist archive', function () {
    $root = dirname(__DIR__);

    // Paths tracked in git with mode 120000 are symlinks.
    $symlinks = array_values(array_filter(array_map(
        'trim',
        explode("\n", (string) shell_exec(
            'cd '.escapeshellarg($root).' && git ls-files -s | awk \'$1=="120000"{print $4}\''
        )),
    )));

    $leaked = array_intersect($symlinks, distArchiveEntries());

    expect(array_values($leaked))->toBe([]);
});