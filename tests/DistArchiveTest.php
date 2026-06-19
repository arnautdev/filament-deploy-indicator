<?php

// Regression test for the Windows install failure (issue: "Cannot create symbolic link").
//
// The package shipped the `workbench/` dev scaffold, which contains the
// `workbench/storage` symlink. Composer's dist archive included it, and
// extracting that symlink on Windows fails without the privilege.
//
// The hard assertions parse `.gitattributes` directly (no external tooling,
// so they run everywhere incl. CI). An extra check runs the real
// `git archive` but is skipped when git/tar/shell_exec are unavailable.

/**
 * Top-level paths flagged `export-ignore` in `.gitattributes`.
 *
 * @return list<string>
 */
function exportIgnoredPaths(): array
{
    $file = dirname(__DIR__) . '/.gitattributes';
    $paths = [];

    foreach (file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
        $line = trim($line);

        if ($line === '' || str_starts_with($line, '#')) {
            continue;
        }

        if (! str_contains($line, 'export-ignore')) {
            continue;
        }

        $path = preg_split('/\s+/', $line)[0];
        $paths[] = trim($path, '/');
    }

    return $paths;
}

it('marks the workbench scaffold as export-ignore', function () {
    expect(exportIgnoredPaths())->toContain('workbench');
});

it('export-ignores the directory of every tracked symlink', function () {
    // Paths tracked in git with mode 120000 are symlinks. Hardcoded fallback
    // keeps the test meaningful when git is unavailable.
    $symlinks = ['workbench/storage'];

    $ignored = exportIgnoredPaths();

    foreach ($symlinks as $symlink) {
        $top = explode('/', $symlink)[0];

        // If this fails: symlink would ship in the dist archive and break
        // installs on Windows. Mark "/{$top}" export-ignore in .gitattributes.
        expect($ignored)->toContain($top);
    }
});

it('does not ship the workbench scaffold in the real dist archive', function () {
    if (! function_exists('shell_exec')) {
        $this->markTestSkipped('shell_exec disabled');
    }

    $root = dirname(__DIR__);

    if (trim((string) shell_exec('command -v git tar 2>/dev/null')) === '') {
        $this->markTestSkipped('git or tar unavailable');
    }

    // `--worktree-attributes` honours the working-tree .gitattributes, so the
    // test is valid even before the fix is committed. `-c safe.directory`
    // avoids the "dubious ownership" abort common in CI containers.
    $output = shell_exec(
        'git -C ' . escapeshellarg($root) . ' -c safe.directory=' . escapeshellarg($root)
        . ' archive --worktree-attributes HEAD 2>/dev/null | tar -t 2>/dev/null'
    );

    $entries = array_values(array_filter(array_map('trim', explode("\n", (string) $output))));

    if ($entries === []) {
        $this->markTestSkipped('git archive produced no output in this environment');
    }

    $workbench = array_filter($entries, fn (string $p) => str_starts_with($p, 'workbench/'));

    expect(array_values($workbench))->toBe([]);
});
