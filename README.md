# NPMplus

This is an improved fork of the nginx-proxy-manager and comes as a pre-built docker image that enables you to easily forward to your websites
running at home or otherwise, including free TLS, without having to know too much about Nginx or Certbot.

- [Quick Setup](#quick-setup)

**Note: no armv7, route53 and aws cloudfront ip ranges support.** <br>
**Note: other Databases like MariaDB/MySQL or PostgreSQL may work, but are unsupported, have no advantage over SQLite and are not recommended.** <br>
**Note: watchtower does not update NPMplus, you need to do it yourself (it will only pull the image, but will not redeploy the container itself).** <br>
**Note: remember to add your domain to the hsts preload list if you use security headers: https://hstspreload.org** <br>

## Project Goal
I created this project to fill a personal need to provide users with an easy way to accomplish reverse
proxying hosts with TLS termination and it had to be so easy that a monkey could do it. This goal hasn't changed.
While there might be advanced options they are optional and the project should be as simple as possible
so that the barrier for entry here is low.

## Upstream Features

- Beautiful and Secure Admin Interface based on [Tabler](https://tabler.github.io)
- Easily create forwarding domains, redirections, streams and 404 hosts without knowing anything about Nginx
- Free trusted TLS certificates using Certbot (Let's Encrypt/other CAs) or provide your own custom TLS certificates
- Access Lists and basic HTTP Authentication for your hosts
- Advanced Nginx configuration available for super users
- User management, permissions and audit log


# List of new features

- Supports HTTP/3 (QUIC) protocol.
- Supports CrowdSec IPS. Please see [here](https://github.com/ZoeyVid/NPMplus#crowdsec) to enable it.
- goaccess included, see compose.yaml to enable, runs by default on https://<ip>:91 (nginx config from [here](https://github.com/xavier-hernandez/goaccess-for-nginxproxymanager/blob/main/resources/nginx/nginx.conf))
- Supports ModSecurity, with coreruleset as an option. You can configure ModSecurity/coreruleset by editing the files in the `/opt/npm/modsecurity` folder (no support from me, you need to write the rules yourself - for CoreRuleSet I can try to help you).
  - By default NPMplus UI does not work when you proxy NPMplus through NPMplus and you have CoreRuleSet enabled, see below
  - ModSecurity by default blocks uploads of big files, you need to edit its config to fix this, but it can use a lot of resources to scan big files by ModSecurity
  - ModSecurity overblocking (403 Error) when using CoreRuleSet? Please see [here](https://coreruleset.org/docs/concepts/false_positives_tuning) and edit the `/opt/npm/modsecurity/crs-setup.conf` file.
    - Try to whitelist the Content-Type you are sending (for example, `application/activity+json` for Mastodon and `application/dns-message` for DoH).
    - Try to whitelist the HTTP request method you are using (for example, `PUT` is blocked by default, which also blocks NPMplus UI).
  - CoreRuleSet plugins are supported, you can find a guide in this readme
- option to load the openappsec attachment module, see compose.yaml for details
- Darkmode button in the footer for comfortable viewing (CSS done by [@theraw](https://github.com/theraw))
- Fixes proxy to https origin when the origin only accepts TLSv1.3
- Only enables TLSv1.2 and TLSv1.3 protocols, also ML-KEM support
- Faster creation of TLS certificates is achieved by eliminating unnecessary nginx reloads and configuration creations.
- Supports OCSP Stapling/Must-Staple for enhanced security (manual certs not supported, see compose.yaml for details)
- Resolved dnspod plugin issue
  - To migrate manually, delete all dnspod certs and recreate them OR change the credentials file as per the template given [here](https://github.com/ZoeyVid/NPMplus/blob/develop/global/certbot-dns-plugins.js)
- Smaller docker image with alpine-based distribution
- Admin backend interface runs with https
- Default page also runs with https
- option to change default TLS cert
- Uses [fancyindex](https://gitHub.com/Naereen/Nginx-Fancyindex-Theme) if used as webserver
- Exposes INTERNAL backend api only to localhost
- Basic security headers are added if you enable HSTS (HSTS has always subdomains and preload enabled)
- access.log is disabled by default, unified and moved to `/opt/npm/nginx/access.log`
- Error Log written to console
- `Server` response header hidden
- PHP optional, with option to add extensions; available packages can added using envs in the compose file, recommended to be used together with PUID/PGID
- Allows different acme servers using env
- Supports up to 99 domains per cert
- Brotli compression can be enabled
- HTTP/2 always enabled with fixed upload
- Allows infinite upload size (may be limited if you use ModSecurity)
- Automatic database vacuum (only sqlite)
- Automatic cleaning of old invalid certbot certs (set CLEAN to true)
- Password reset (only sqlite) using `docker exec -it npmplus password-reset.js USER_EMAIL PASSWORD`
- multi lang support, if you want to add an language, see this commit as an example: https://github.com/ZoeyVid/NPMplus/commit/a026b42329f66b89fe1fbe5e6034df5d3fc2e11f (implementation based on [@lateautumn233](https://github.com/lateautumn233) fork)
- See the compose file for all available options
- many env options optimized for network_mode host (ports/ip bindings)
- allow port range in streams and $server_port as a valid input
- improved regex checks for inputs
- dns secrets are not mounted anymore, since they are saved in the db and rewritten on every container start, so they don't need to be mounted
- fixed smaller issues/bugs
- other small changes/improvements

## migration
- **NOTE: migrating back to the original is not possible**, so make first a **backup** before migration, so you can use the backup to switch back
- please delete all certs using dnspod as dns provider and recreate them after migration, since the certbot plugin used was replaced
- stop nginx-proxy-manager download the latest compose.yaml, adjust your paths (of /etc/letsencrypt and /data) to the ones you used with nginx-proxy-manager and adjust the envs of the compose file how you like it and then deploy it
- you can now remove the /etc/letsencrypt mount, since it was moved to /data while migration, and redeploy the compose file
- since many buttons changed, please check if they are still correct for every host you have.
- maybe setup crowdsec (see below)
- please report all (migration) issues you may have

# Quick Setup
1. Install Docker and Docker Compose (podman or docker rootless may also work)
- [Docker Install documentation](https://docs.docker.com/engine/install)
- [Docker Compose Install documentation](https://docs.docker.com/compose/install/linux)
2. Download this [compose.yaml](https://raw.githubusercontent.com/ZoeyVid/NPMplus/refs/heads/develop/compose.yaml) (or use its content as a portainer stack)
3. adjust TZ and ACME_EMAIL to your values and maybe adjust other env options to your needs.
4. start NPMplus by running (or deploy your portainer stack)
```bash
docker compose up -d
```
5. Log in to the Admin UI
When your docker container is running, connect to it on port `81` for the admin interface.
Sometimes this can take a little bit because of the entropy of keys.
You may need to open port 81 in your firewall.
You may need to use another IP-Address.
[https://127.0.0.1:81](https://127.0.0.1:81)
Default Admin User:
```
Email:    admin@example.org
Password: iArhP1j7p1P6TA92FA2FMbbUGYqwcYzxC4AVEe12Wbi94FY9gNN62aKyF1shrvG4NycjjX9KfmDQiwkLZH1ZDR9xMjiG2QmoHXi
```
Immediately after logging in with this default user you will be asked to modify your details and change your password.

# Crowdsec
Note: Using Immich behind NPMplus with enabled appsec causes issues, see here: [#1241](https://github.com/ZoeyVid/NPMplus/discussions/1241)
1. Install crowdsec and the ZoeyVid/npmplus collection for example by using crowdsec container at the end of the compose.yaml
2. set LOGROTATE to `true` in your `compose.yaml` and redeploy
3. open `/opt/crowdsec/conf/acquis.d/npmplus.yaml` (path may be different depending how you installed crowdsec) and fill it with:
```yaml
filenames:
  - /opt/npm/nginx/access.log
labels:
  type: npmplus
---
source: docker
container_name:
 - npmplus
labels:
  type: npmplus
---
source: docker
container_name:
 - npmplus
labels:
  type: modsecurity
---
listen_addr: 0.0.0.0:7422
appsec_config: crowdsecurity/appsec-default
name: appsec
source: appsec
labels:
  type: appsec
# if you use openappsec you can enable this
#---
#source: docker
#container_name:
# - openappsec-agent
#labels:
#  type: openappsec
```
4. make sure to use `network_mode: host` in your compose file
5. run `docker exec crowdsec cscli bouncers add npmplus -o raw` and save the output
6. open `/opt/npm/crowdsec/crowdsec.conf`
7. set `ENABLED` to `true`
8. use the output of step 5 as `API_KEY`
9. save the file
10. redeploy the `compose.yaml`

# coreruleset plugins
1. Download the plugin (all files inside the `plugins` folder of the git repo), most time: `<plugin-name>-before.conf`, `<plugin-name>-config.conf` and `<plugin-name>-after.conf` and sometimes `<plugin-name>.data` and/or `<plugin-name>.lua` or somilar files
2. put them into the `/opt/npm/modsecurity/crs-plugins` folder
3. maybe open the `/opt/npm/modsecurity/crs-plugins/<plugin-name>-config.conf` and configure the plugin

# Use as webserver
1. Create a new Proxy Host
2. Set `Scheme` to `https`, `Forward Hostname / IP` to `0.0.0.0`, `Forward Port` to `1` and enable `Websockets Support` (you can also use other values, since these get fully ignored)
3. Maybe set an Access List
4. Make your TLS Settings
5.
a) Custom Nginx Configuration (advanced tab), which looks the following for file server:
- Note: the slash at the end of the file path is important
```
location / {
    include conf.d/include/always.conf;
    alias /var/www/<your-html-site-folder-name>/;
    fancyindex off; # alternative to nginxs "index" option (looks better and has more options)
}
```
b) Custom Nginx Configuration (advanced tab), which looks the following for file server and **php**:
- Note: the slash at the end of the file path is important
- Note: first enable `PHP82`, `PHP83` and/or `PHP84` inside your compose file
- Note: you can replace `fastcgi_pass php82;` with `fastcgi_pass php83;`/`fastcgi_pass php84;`
- Note: to add more php extension using envs you can set in the compose file
```
location / {
    include conf.d/include/always.conf;
    alias /var/www/<your-html-site-folder-name>/;
    fancyindex off; # alternative to nginxs "index" option (looks better and has more options)

    location ~ [^/]\.php(/|$) {
        fastcgi_pass php82;
        fastcgi_split_path_info ^(.+?\.php)(/.*)$;
        if (!-f $document_root$fastcgi_script_name) {
            return 404;
        }
    }
}
```

### prerun scripts (EXPERT option) - if you don't know what this is, ignore it
run order: entrypoint.sh (prerun scripts) => start.sh => launch.sh <br>
if you need to run scripts before NPMplus launches put them under: `/opt/npm/prerun/*.sh` (please add `#!/usr/bin/env sh` / `#!/usr/bin/env bash` to the top of the script) <br>
you need to create this folder yourself - **NOTE:** I won't help you creating those patches/scripts if you need them you also need to know how to create them

## Contributing
All are welcome to create pull requests for this project, but this does not mean it will be merged.

# Please report Bugs first to this fork before reporting them to the upstream Repository
## Getting Help
1. [Support/Questions](https://github.com/ZoeyVid/NPMplus/discussions)
2. [Reddit](https://reddit.com/r/NPMplus)
3. [Bugs](https://github.com/ZoeyVid/NPMplus/issues)
