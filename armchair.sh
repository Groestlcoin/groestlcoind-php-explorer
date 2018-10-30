#!/bin/bash
set -x
#@@GROESTLCOIND@@
#@@BLOCKCHAINNAME@@
#@@COUCHPREFIX@@
#@@SYM@@
GROESTLCOIND=${GROESTLCOIND_:="~/groestlcoind"}
BLOCKCHAINNAME=${BLOCKCHAINNAME_:="groestlcoin"}
COUCHPREFIX=${COUCHPREFIX_:="http://127.0.0.1:5984"}
SYM=${SYM_:="grs"}

srcfiles=src/
tokens=(GROESTLCOIND BLOCKCHAINNAME COUCHPREFIX SYM)
mkdir -p target
cp src/* target

for i in ${tokens[*]}; do
sed -i 's,@@'${i}'@@,'$(eval echo \$${i})',g' target/*
done

pushd target
chmod a+x *.sh
bash ./init.sh
bash ./update.sh
popd
set +x
