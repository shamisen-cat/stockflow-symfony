# Using AI Coding Agents with Dev Containers

This project ships with a [Dev Container](https://containers.dev/) that includes a
**network sandbox by default**. Outbound traffic is restricted to an allowlist so AI
coding agents (and other tools) cannot reach arbitrary internet hosts.

No AI coding agent is pre-installed. Install the agent you want (for example
[OpenCode](https://opencode.ai), [Claude Code](https://claude.ai/claude-code), or
[OpenAI Codex CLI](https://github.com/openai/codex)) inside the container, and add any
extra API domains to the firewall allowlist if needed.

Project context for agents that understand `AGENTS.md` is provided via
`.devcontainer/AGENTS.md` (symlinked to `/app/AGENTS.md` on container create).

## Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (or any Docker-compatible runtime)
- A [Dev Container–compatible editor](https://containers.dev/supporting#editors)
  (Visual Studio Code, Cursor, PhpStorm, …)
- A valid subscription or API key for the agent you want to use

## Quick Start

1. Open the project in your editor.
2. When prompted "Reopen in Container", click **Reopen in Container**.
   Alternatively, open the Command Palette (`Ctrl+Shift+P` / `Cmd+Shift+P`) and run
   **Dev Containers: Reopen in Container**.
3. Wait for the container to build and start. On each container start, the
   `postStartCommand` runs `.devcontainer/init-firewall.sh` automatically.
4. Install and run your preferred agent inside the container
   (see [Installing an agent](#installing-an-agent)).

The firewall is active regardless of which agent you use.

## Network Sandboxing

The Dev Container includes a firewall script (`.devcontainer/init-firewall.sh`) that
locks down outbound network access using `iptables` and `ipset`. Only the following
destinations are allowed by default:

| Destination                                                   | Reason                                    |
| ------------------------------------------------------------- | ----------------------------------------- |
| Packagist (`packagist.org`)                                   | PHP/Composer dependencies                 |
| npm registry (`registry.npmjs.org`)                           | Node.js dependencies                      |
| jsDelivr (`cdn.jsdelivr.net`, `data.jsdelivr.com`)            | AssetMapper / web assets                  |
| Iconify (`iconify.design`)                                    | Symfony UX Icons                          |
| GitHub (`github.com`, `release-assets.githubusercontent.com`) | Git operations, release assets            |
| Visual Studio / Cursor Marketplace and update endpoints       | Extension downloads and updates           |
| Anthropic (`anthropic.com`)                                   | Claude Code backend (when you install it) |
| Sentry, Statsig                                               | Telemetry used by some agents             |
| Host gateway IP                                               | Communication with Docker host            |

All other outbound connections are **rejected**. The firewall uses
[dnsmasq](https://thekelleys.org.uk/dnsmasq/doc.html) to dynamically resolve
and whitelist IPs for allowed domains, handling CDN IP rotation gracefully.

Inbound connections from the host gateway IP are allowed on all ports,
and ports 80, 443 (TCP), and 443 (UDP/HTTP3) are open to any source
so you can access your Symfony app from the host browser.

## Customizing the Allowed Domains

To allow additional domains (e.g., a private registry or an agent API), edit
`.devcontainer/init-firewall.sh` and add an `ipset=` line in the dnsmasq
configuration block:

```bash
ipset=/your-domain.com/allowed-domains
```

Then rebuild the Dev Container for the changes to take effect.

## Installing an Agent

The network sandbox and `.devcontainer/AGENTS.md` work with any AI coding agent.
Install the agent, whitelist any domains it needs, and run it inside the container.

### OpenCode

1. Add your model provider’s API domain to the firewall allowlist if it is not
   already covered (see [Customizing the Allowed Domains](#customizing-the-allowed-domains)).
2. Install and run OpenCode inside the container:

   ```console
   curl -fsSL https://opencode.ai/install | bash
   opencode
   ```

### Claude Code

1. `anthropic.com` is already on the allowlist. Install Claude Code via a
   [Dev Container feature](https://github.com/anthropics/devcontainer-features)
   and/or VS Code / Cursor extension in `.devcontainer/devcontainer.json`, then
   rebuild the container.
2. Or install and run from the terminal after the container is up (follow
   Anthropic’s current install instructions).

If you want Claude Code to skip permission prompts (sometimes called YOLO /
bypass-permissions mode), configure that in the editor or pass the appropriate
CLI flag yourself. This project does **not** enable that mode by default.

### OpenAI Codex CLI

1. Add the OpenAI API domain to the firewall allowlist:

   ```bash
   ipset=/api.openai.com/allowed-domains
   ```

2. Install and run Codex inside the container:

   ```console
   npm install -g @openai/codex
   export OPENAI_API_KEY=your-key
   codex
   ```

### Other Agents

1. Add the agent’s API domain(s) to the firewall allowlist.
2. Install the agent inside the container.
3. Run it — `.devcontainer/AGENTS.md` provides project context for agents that
   support the convention.

## Using Without Visual Studio Code

The Dev Container configuration works with any tool that supports the
[Dev Container specification](https://containers.dev/), including:

- [Dev Container CLI](https://github.com/devcontainers/cli) (`devcontainer up`)
- [GitHub Codespaces](https://github.com/features/codespaces)
- JetBrains IDEs (with the Dev Containers plugin)
- [Cursor](https://cursor.com/)

## Troubleshooting

### Firewall blocks a required domain

If your agent or Composer/npm fails to reach a service, check the firewall
logs and add the domain to the dnsmasq allowlist as described above.

### Container fails to start

Ensure Docker is running and that you have allocated enough resources
(at least 2 GB of RAM for the container). The firewall setup requires
`NET_ADMIN` capability, which the Dev Container configures automatically
via Docker Compose.
