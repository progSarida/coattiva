<?php

const ACISERVER = "vpn2.aci.it";
const ACISERVERIP = "77.73.56.56";
const ACIPW = 66278331;
const ACIUSR = "ACIVPN11190";

const ACIVPN_CMD = 'echo "'.ACIPW.'" | sudo openconnect --user='.ACIUSR.' --passwd-on-stdin --no-xmlpost --no-dtls -v '.ACISERVER.' >/dev/null 2>/dev/null &';
const ACICHECK_CMD = 'ip route show to match '.ACISERVERIP;
const ACIVPN_KILL = 'pkill openconnect';