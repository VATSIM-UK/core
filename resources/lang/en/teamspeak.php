<?php


return [
    'registration.notfound.poke' => 'No registration found. Visit https://core.vatsim-uk.co.uk to register.',
    'registration.notfound.kick' => 'No registration found.',
    'registration.notfound.exception' => 'No registration found for :dbid',
    'ban.network.ban' => 'You are currently serving a VATSIM.net ban.',
    'ban.system.ban' => 'You are currently serving a VATSIM UK ban.',
    'inactive' => 'Your VATSIM membership is inactive - visit https://cert.vatsim.net/vatsimnet/statcheck.html',
    'notification.mandatory.notify' => 'You must accept the new notifications that are published at https://core.vatsim-uk.co.uk',
    'notification.mandatory.poke' => 'You cannot connect to TeamSpeak until you read the notifications at https://core.vatsim-uk.co.uk',
    'notification.mandatory.kick' => 'You must accept the latest important notifications.',
    'notification.important.poke.1' => 'You have new important notifications to accept at https://core.vatsim-uk.co.uk',
    'notification.important.poke.2' => 'These notifications are highly relevent. Please read them.',
    'nickname.invalid.poke1' => 'Please use your full VATSIM-registered name - you may check this at https://core.vatsim-uk.co.uk/',
    'nickname.invalid.poke2' => 'If you believe this is a mistake, please contact Web Services via https://helpdesk.vatsim-uk.co.uk/',
    'nickname.invalid.kick' => 'Please use your full VATSIM-registered name.',
    'nickname.partiallyinvalid.poke1' => 'Your current nickname is not allowed. Ensure that your name is your VATSIM-registered one.',
    'nickname.partiallyinvalid.poke2' => ' If you are controlling on the network, please view your teamspeak messages for the correct format.',
    'nickname.partiallyinvalid.note' => 'Please note; the network data is only updated every 3 minutes, so it is possible that you are connected. In which case, your data should be updated before the timer finishes. As an example, your nickname must match the following format: :example. Ensure that the callsign is as shown on your ATC client. Please change it to the correct name within 3 minutes. If you believe this is a mistake, please contact Web Services via helpdesk.vatsim.uk',
    'idle.message' => 'You have been idle in TeamSpeak for at least :idleTime minutes. We encourage members to disconnect if they plan to be away for extended periods. You will be disconnected if you are idle for greater than :maxIdleTime minutes.',
    'idle.poke' => 'You have been idle for more than :idleTime minutes. If you continue to be idle, you will be removed.',
    'idle.kick.poke.1' => 'You have been removed from the TeamSpeak server for remaining idle for more than :maxIdleTime minutes.',
    'idle.kick.poke.2' => 'You are free to reconnect, however please do not remain idle for extended periods.',
    'idle.kick.reason' => 'Idle timeout exceeded.',
];
