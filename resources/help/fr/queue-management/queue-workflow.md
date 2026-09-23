---
title: Gestion de la File d'Attente et Flux de Tickets
category: Queue Management
description: Guide complet des modes Simple et Avancé, appeler le suivant, mettre en attente, passer, annuler, et cycle de vie du ticket.
roles: [admin, secretary, staff, customer]
routes: [queue.index, queue.simple.index, tickets.index, tickets.show]
---

# Gestion de la File d'Attente et Flux de Tickets

Noubtigo offre un système de gestion de file d'attente intelligent prenant en charge deux modes opérationnels : **Mode Simple** et **Mode Avancé**.

---

## Comparaison des Modes

| Fonctionnalité | Mode Simple | Mode Avancé |
| :--- | :--- | :--- |
| **Idéal pour** | Guichet unique, service rapide | Cliniques multi-salles, bureaux d'entreprise |
| **Actions** | Suivant, Passer, Terminer | Suivant, Attente, Reprendre, Passer, Transférer, Priorité |
| **VIP / Priorité** | Basique | Notation de priorité avancée avec intégration des rendez-vous |

---

## Contrôles Principaux

1. **Émettre un Ticket** : Génère un numéro séquentiel unique (ex: `A-014`).
2. **Appeler Suivant** : Déclenche une alerte visuelle et sonore sur les écrans publics.
3. **Passer** : Utilisé lorsqu'un client est absent lors de l'appel.
4. **Mettre en Attente (Hold)** : Pause temporaire de la prise en charge.
5. **Reprendre (Resume)** : Replacement du client en attente active.
6. **Annuler / Rouvrir** : Annule ou restaure une erreur d'annulation.
