# Changelog
All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [2.0.1] - 2023-06-24
### Changed
- Fixed bug with showing &nbsp; in browser for Zabbix >= 6.0.18

## [4.1.1] - 2023-06-11
### Changed
- Fixed bug with hiding childeren groups at all levels.

## [4.1.0] - 2023-06-08
### Changed
- Show group tree expanded at first load
- Fixed bug with showing &nbsp; in browser for Zabbix >= 6.4.3

## [4.0.0] - 2023-04-08
### Changed
- Updated to work with Zabbix 6.4

## [3.0.0] - 2022-07-24
### Changed
- Updated to work with Zabbix 6.2.0

## [2.0.0] - 2022-02-16
### Changed
- Updated to work with Zabbix 6.0.0

## [1.3.0] - 2021-11-12
### Changed
- Lots of code re-written
- All calculations moved from View to Controller
- Performance improvements (should be noticable when there is a lot of groups)
- Fix number of hosts and number of problems calculation per group
- Change the way of presenting Groups with paging (too many hosts belong to one group and to different groups)

## [1.2.0] - 2021-08-20
### Changed
- Alphabetically arranged groups in the tree branches
- Add space between the group name and the host number

## [1.1.0] - 2021-08-15
### Added
- show number of hosts in groups and subgroups
- show active triggers for groups

## [1.0.3] - 2021-08-11
### Changed
- fixed pagination

## [1.0.2] - 2021-08-11
### Changed
- fixed issue with "fake" group IDs duplication leading to hiding/showing multiple groups simultaneously

## [1.0.1] - 2021-08-10
### Changed
- show all parent groups that do not exist in Zabbix DB or do not have any hosts

## [1.0.0] - 2021-08-08
### Added
- the first version released
