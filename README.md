# zabbix-module-hosts-tree
Written according to Zabbix official documentation [Modules](https://www.zabbix.com/documentation/current/en/devel/modules/file_structure)

A Zabbix module to show groups/hosts as a tree under Monitoring -> Hosts Tree menu item in Zabbix.
![screenshot](screenshots/zabbix-module-hosts-tree-1.png)

IMPORTANT: pick module version according to Zabbix version:
| Module version | Zabbix version |
|:--------------:|:--------------:|
|     v1.3.0     |     5.4        |
|     v2.0.1     |     6.0        |
|     v3.0.0     |     6.2        |
|     v4.1.1     |     6.4        |
|     v6.0.1     |     7.0        |
|     v6.1.1     |     7.2        |

# How to use
1) Create a folder in your Zabbix server modules folder (by default /usr/share/zabbix/) and copy contents of this repository into folder `zabbix-module-hosts-tree`.
2) Go to Administration -> General -> Modules click Scan directory and enable the module. You should get new 'Hosts tree' menu item under Monitoring.

## Authors
See [Contributors](https://github.com/BGmot/zabbix-module-hosts-tree/graphs/contributors)
