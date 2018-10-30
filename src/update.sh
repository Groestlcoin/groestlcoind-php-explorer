#!/bin/bash

for (( i = $(@@GROESTLCOIND@@  getblockcount  ) ; i != 0 ; i-- )); do curl -f -XPUT  @@COUCHPREFIX@@/@@SYM@@_blocks/$i -d@<(@@GROESTLCOIND@@  getblockbynumber ${i} true )||break ;  done
