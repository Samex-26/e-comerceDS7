# Graph Report - .  (2026-07-21)

## Corpus Check
- cluster-only mode — file stats not available

## Summary
- 634 nodes · 1068 edges · 73 communities (44 shown, 29 thin omitted)
- Extraction: 97% EXTRACTED · 3% INFERRED · 0% AMBIGUOUS · INFERRED: 35 edges (avg confidence: 0.8)
- Token cost: 0 input · 0 output

## Graph Freshness
- Built from commit: `61c5731c`
- Run `git rev-parse HEAD` and compare to check if the graph is stale.
- Run `graphify update .` after code changes (no API cost).

## Community Hubs (Navigation)
- bootstrap.bundle.min.js
- ln
- remove
- ci
- .hide
- rt
- Database
- xn
- Controller
- Validator
- Sanitizer
- Ni
- classmap
- N
- VentaModel
- Y
- ProductoController
- Producto
- Ti
- AuthController
- FacturaController
- Model
- vi
- CategoriaController
- Categoria
- Usuario
- FirmaDigitalService
- stitch-mcp
- CarritoController
- VentaController
- ProveedorModel
- InventarioModel
- VisitaModel
- InventarioController
- ProveedorController
- UsuarioController
- ContactoController
- graphify.js
- main.js

## God Nodes (most connected - your core abstractions)
1. `ln` - 38 edges
2. `rt` - 28 edges
3. `Validator` - 27 edges
4. `Sanitizer` - 26 edges
5. `Controller` - 25 edges
6. `remove()` - 23 edges
7. `ci` - 22 edges
8. `Ni` - 20 edges
9. `N()` - 18 edges
10. `xn` - 18 edges

## Surprising Connections (you probably didn't know these)
- `AuthController` --inherits--> `Controller`  [EXTRACTED]
  app/controllers/AuthController.php → app/core/Controller.php
- `CarritoController` --inherits--> `Controller`  [EXTRACTED]
  app/controllers/CarritoController.php → app/core/Controller.php
- `CategoriaController` --inherits--> `Controller`  [EXTRACTED]
  app/controllers/CategoriaController.php → app/core/Controller.php
- `ContactoController` --inherits--> `Controller`  [EXTRACTED]
  app/controllers/ContactoController.php → app/core/Controller.php
- `DashboardController` --inherits--> `Controller`  [EXTRACTED]
  app/controllers/DashboardController.php → app/core/Controller.php

## Import Cycles
- None detected.

## Communities (73 total, 29 thin omitted)

### Community 0 - "bootstrap.bundle.min.js"
Cohesion: 0.09
Nodes (45): ae(), Be(), Bt(), Ce(), D(), ee(), F(), Fe() (+37 more)

### Community 1 - "ln"
Cohesion: 0.07
Nodes (4): dn, ft, getSelectorFromElement(), ln

### Community 2 - "remove"
Cohesion: 0.07
Nodes (4): $, pi, remove(), Ui

### Community 3 - "ci"
Cohesion: 0.07
Nodes (3): ci, q, W

### Community 6 - "Database"
Cohesion: 0.10
Nodes (6): DashboardController, Database, PDO, Router, IdiomaHelper, self

### Community 8 - "Controller"
Cohesion: 0.14
Nodes (3): CookieController, InicioController, Controller

### Community 12 - "classmap"
Cohesion: 0.13
Nodes (14): autoload, classmap, description, name, require, php, tecnickcom/tcpdf, type (+6 more)

### Community 21 - "Model"
Cohesion: 0.20
Nodes (4): Model, PDO, CookieConsentimientoModel, Idioma

### Community 26 - "FirmaDigitalService"
Cohesion: 0.25
Nodes (3): FirmaDigitalService, PasswordHasherService, CriptoServiceInterface

### Community 27 - "stitch-mcp"
Cohesion: 0.22
Nodes (8): X-Goog-Api-Key, mcp, stitch-mcp, $schema, enabled, headers, type, url

## Knowledge Gaps
- **16 isolated node(s):** `name`, `description`, `type`, `php`, `tecnickcom/tcpdf` (+11 more)
  These have ≤1 connection - possible missing edges or undocumented components.
- **29 thin communities (<3 nodes) omitted from report** — run `graphify query` to explore isolated nodes.

## Suggested Questions
_Questions this graph is uniquely positioned to answer:_

- **Why does `Controller` connect `Controller` to `InventarioController`, `ProveedorController`, `UsuarioController`, `ContactoController`, `Database`, `Validator`, `Sanitizer`, `ProductoController`, `AuthController`, `FacturaController`, `CategoriaController`, `CarritoController`, `VentaController`?**
  _High betweenness centrality (0.073) - this node is a cross-community bridge._
- **Why does `Model` connect `Model` to `VisitaModel`, `Database`, `VentaModel`, `Producto`, `FacturaController`, `Categoria`, `Usuario`, `ProveedorModel`, `InventarioModel`?**
  _High betweenness centrality (0.065) - this node is a cross-community bridge._
- **Why does `ln` connect `ln` to `bootstrap.bundle.min.js`, `remove`, `ci`?**
  _High betweenness centrality (0.048) - this node is a cross-community bridge._
- **Are the 14 inferred relationships involving `Validator` (e.g. with `.procesarRegistro()` and `.actualizar()`) actually correct?**
  _`Validator` has 14 INFERRED edges - model-reasoned connections that need verification._
- **Are the 15 inferred relationships involving `Sanitizer` (e.g. with `.procesarRegistro()` and `.actualizar()`) actually correct?**
  _`Sanitizer` has 15 INFERRED edges - model-reasoned connections that need verification._
- **What connects `name`, `description`, `type` to the rest of the system?**
  _16 weakly-connected nodes found - possible documentation gaps or missing edges._
- **Should `bootstrap.bundle.min.js` be split into smaller, more focused modules?**
  _Cohesion score 0.08832425892316999 - nodes in this community are weakly interconnected._