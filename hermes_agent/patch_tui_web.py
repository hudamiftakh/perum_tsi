with open('/home/wabotweb/.hermes/hermes-agent/hermes_cli/source_build.py', 'r') as f:
    text = f.read()

target = """def build_source_tui(project_root: Path, *, env: dict) -> None:
    run_source_script(project_root, "scripts/build/tui.mjs", env=env, label="Building the TUI")


def build_source_web(project_root: Path, *, env: dict, icons: Path | None = None) -> None:
    # Default-brand icons are committed; installs never render them.
    icons = icons or project_root
    run_source_script(project_root, "scripts/build/web.mjs", "--source", str(project_root),
                      "--icons", str(icons), "--out", str(project_root / "hermes_cli/web_dist"), env=env,
                      label="Building the web UI")"""

replacement = """def build_source_tui(project_root: Path, *, env: dict) -> None:
    env["GOMAXPROCS"] = "1"
    try:
        run_source_script(project_root, "scripts/build/tui.mjs", env=env, label="Building the TUI")
    except Exception as e:
        print(f"  ⚠ Skipping TUI desktop bundle on headless server: {e}")


def build_source_web(project_root: Path, *, env: dict, icons: Path | None = None) -> None:
    env["GOMAXPROCS"] = "1"
    try:
        icons = icons or project_root
        run_source_script(project_root, "scripts/build/web.mjs", "--source", str(project_root),
                          "--icons", str(icons), "--out", str(project_root / "hermes_cli/web_dist"), env=env,
                          label="Building the web UI")
    except Exception as e:
        print(f"  ⚠ Skipping Web UI bundle on headless server: {e}")"""

if target in text:
    text = text.replace(target, replacement, 1)
    with open('/home/wabotweb/.hermes/hermes-agent/hermes_cli/source_build.py', 'w') as f:
        f.write(text)
    print("PATCHED build_source_tui & build_source_web successfully!")
else:
    print("TARGET NOT FOUND in source_build.py")
