# ProLink — GitHub Project board (backlog)

Use this file to **fill your GitHub Project board** in a few minutes.

## Create the board on GitHub

1. Open the repo: `https://github.com/medali141/Esprit-PW-2A30-2526-ProLink`
2. Tab **Projects** → **New project**
3. Choose **Board** (or Table with a **Status** field)
4. Name it e.g. `ProLink — delivery`
5. **Link** this repository to the project
6. Add columns if needed: **Todo** · **In progress** · **Done**

## Add cards (copy each line as one item)

Create a **draft item** or **issue** per line, then drag to the right column.

### Todo (backlog)

- [ ] Open PR: merge branch `gestion-d'achats` → `main` (or resolve conflicts first)
- [ ] Review open PR `#10` (`user` → `main`) vs commerce branch — avoid duplicate / conflicting merges
- [ ] Fresh DB: import `base/prolink.sql` on a clean MySQL DB and document host/user in `config` (no secrets in git)
- [ ] Smoke test FO: catalogue → panier → checkout → `mesCommandes.php`
- [ ] Smoke test BO: `commerceHub.php` → produits CRUD → commandes → `detailCommande.php` (statut + suivi)
- [ ] Smoke test vendeur: `mesProduits.php` → add/edit product → visible catalogue if `actif`
- [ ] Verify `forms-validation.js` on checkout and panier (invalid CP, empty address)
- [ ] Dark mode: BO commerce pages + sidebar toggle
- [ ] Mobile: tables list produits / commandes (horizontal scroll or stacked layout)
- [ ] Security: confirm admin-only routes; entrepreneur cannot access `view/BackOffice/*`
- [ ] Replace demo passwords in SQL for production / demo vidéo (document test accounts only)
- [ ] README: one section “Commerce module” with URLs (`/view/FrontOffice/catalogue.php`, BO hub path)

### In progress

- [ ] _(move here what the team is doing this week)_

### Done

- [ ] _(move here completed items, e.g. “Hub commerce + commerce.css wired”)_

---

## Optional: use Issues instead of draft cards

1. **Issues** → **New issue** for each important task  
2. In the issue sidebar, **add to Project** and set **Status**  
3. Benefit: PRs can close issues (`Fixes #12`)

---

## Suggested assignees

Split **Todo** items among teammates (admin BO, FO boutique, SQL/demo, doc).
