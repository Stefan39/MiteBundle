# MiteBundle

> Ein Symfony-Bundle für die Zeiterfassung mite: typisierte Services und DTOs für jede API-Ressource, konfiguriert mit zwei Umgebungsvariablen.

[![CI](https://github.com/Stefan39/MiteBundle/actions/workflows/ci.yml/badge.svg)](https://github.com/Stefan39/MiteBundle/actions/workflows/ci.yml)
[![License: MIT](https://img.shields.io/badge/License-MIT-blue.svg)](LICENSE)
[![PHPStan](https://img.shields.io/badge/PHPStan-level%208-brightgreen)](phpstan.neon)

[English version](README.md)

<p align="center">
  <img src="docs/demo/demo.gif" alt="Heutige Zeiteinträge mit TimeEntriesApi auslesen (Demodaten)" width="720">
</p>

## Wofür?

Wer mite nutzt und Zeiten in eigene Symfony-Anwendungen holen will – für Rechnungen, Auswertungen oder Dashboards –, bekommt hier einen vollständigen, getesteten Zugang zur mite-API. Statt loser Arrays liefert das Bundle feste Datentypen, so dass Fehler früh auffallen.

## Schnellstart

```bash
composer require stejac/mite-bundle
```

```yaml
# config/packages/mite.yaml
mite:
    domain: '%env(MITE_DOMAIN)%'   # "acme" für acme.mite.de
    api_key: '%env(MITE_API_KEY)%'
```

```php
$entries = $timeEntries->list(new TimeEntryFilter(currentUser: true, at: Period::ThisWeek));
```

## Funktionen

Konto, Zeiteinträge (inkl. gruppierter Auswertungen), Stoppuhr, Lesezeichen, Kunden, Projekte, Leistungen, Benutzer und der Änderungs-Feed – alles, was die mite-API anbietet. Details und Beispiele stehen in der [englischen README](README.md).

## Sicherheit

Der API-Schlüssel wird nur per Header an `https://{domain}.mite.de` gesendet. Sicherheitslücken bitte vertraulich melden, siehe [SECURITY.md](SECURITY.md).

## Über

Entwickelt von [Stefan Jacomeit](https://www.jacomeit.de) – freiberuflicher PHP/Symfony-Entwickler mit über 25 Jahren Erfahrung.

Inoffiziell. Keine Verbindung zu mite GmbH und nicht von ihr unterstützt. mite ist ein Produkt der mite GmbH.

## Lizenz

[MIT](LICENSE)
