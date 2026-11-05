# Tool logo sources

Logos are hosted locally to avoid external image requests. Product names and logos belong to their respective owners.

- Docker, Git, Composer, Postman, Insomnia, VS Code, Bitbucket, GitHub, Jira, DBeaver, MySQL (for MySQL Workbench), PostgreSQL, Laravel, C++, and Java: [Devicon](https://github.com/devicons/devicon), original SVG variants.
- Notepad++: [Simple Icons](https://github.com/simple-icons/simple-icons/blob/develop/icons/notepadplusplus.svg), colored green for the dark backdrop.
- Claude (for Claude Code): [Simple Icons](https://github.com/simple-icons/simple-icons/blob/develop/icons/claude.svg).
- OpenAI (for OpenAI Codex): [Lobe Icons](https://github.com/lobehub/lobe-icons/blob/master/packages/static-svg/icons/openai.svg).

Claude Code uses the Claude brand mark; Codex uses the OpenAI brand mark; MySQL Workbench uses the MySQL dolphin mark. The portfolio already lists Claude Code, so the requested “Cloud Code” is interpreted as Claude Code.

## Manage logos in the CMS

Open `/admin` → **Tool logos**. Use **Add logo** or **Edit** to set the display name, upload a PNG, WebP, JPG, or GIF, choose original colors or a white logo, and set its display order (lower numbers appear first). Transparent PNG or WebP images work best on the dark backdrop. Existing bundled SVG logos remain usable; new uploads use raster formats.

Saved changes appear immediately on the database-backed portfolio. Use **Publish Static** from the dashboard to export the updated logo records, stage uploaded images, and rebuild the static portfolio. Deleting every logo restores the binary-digit globe.
