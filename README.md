# true-random-numbers-from-microphone
true random numbers from microphone

From a microphone input, I am generating true random numbers that pass the "rngtest" command.

Regarding looking at this same data as signal rather than random noise, see my 2021/01/27 update below.

USAGE

Will result in around 1 second's worth of random hex numbers covering the screen:

php index.php -x -d1
OR
php index.php -x -d=1

If you have rngtest:

php index.php -raw -d1 | rngtest -c 30
See sample output below.


With the following command, if you have aplay, turn your speakers down and play 12 seconds of static.  If you don't have aplay, you can't use this program anyhow 
because it needs arecord, which is part of the same package (see below).

php index.php -raw -d1 | aplay

-d1 means 1 second of input, but the output is 12 seconds because it's a high-quality sample downgraded quite a bit, by default (see more specs below).

If you have rngd installed (part of rng-tools, see more below):

od -N 1000 -x /dev/random & php index.php -raw -d1 2> /dev/null | sudo rngd -f -r /dev/stdin 2> /dev/null


MORE DETAILS

I am using a desktop (Ubuntu Linux) microphone input with no microphone attached.  On the software / settings side, the microphone input is turned all the way up.  

For each sound sample, I am taking the smallest-order byte out of 4 byte precision.

Higher bytes will usually be 0 or a fixed number because there is no microphone attached and thus no large dynamic (quiet to loud) variation.  I am assuming I am 
picking up electric noise.  When a sample file is played, it sounds like white noise, although I note below that I get radio wave "whistlers."

My settings are

arecord -f S32_LE -c 2 -d 1 -r 48000 --device="hw:0,0" 

which means: 32 bit (4 byte) signed precision sound samples, little endian (bytes with lower addresses are the smallest order bits), 2 channels (stereo), 
48,000 samples per second.  I think I have to record in stereo with my hardware, and 32 bits and 48,000 samples are the highest my hardware / software supports.  
--device="hw:0,0" identifies the microphone in my case.  Future work may include automatic discovery of the microphone.

I have done limited experimentation with a microphone attached.  I know that if the microphone is gently tapped with my finger, the sound is loud enough to 
spike the data and make it non-random.  An obvious experiment is to try with the software input settings turned to zero / muted, which should either pass or 
fail on a laptop mic that can't be easily turned off physically.

My first versions only used one of the 2 channels.  Further testing shows that using both stereo channels is fine.  

I've done several tests up to 20 seconds.  The failure rate seems comparable to a system using rngd with the rdrand CPU instructions available.  I understand that 
rdrand is known to be intentionally compromised, so "my" technique may still be useful.  "My" technique is almost certainly much slower than rdrand, though, given 
CPU-generated randomness versus a sample rate correlated to human hearing perception. 

44 is the size of a WAV (RIFF) header - http://www.topherlee.com/software/pcm-tut-wavformat.html

I have to throw out between 40,000 - 50,000 bytes because they are not random until that range.  I am not sure why this is.  One theory is that it takes a moment for the
recording system to calibrate how loud the input is.

Another theory is based on something like this:

arecord -f S32_LE -c 2 -d 4 -r 48000 --device="hw:0,0" -d 1 | aplay

I sometimes get "pops" at the very beginning.  That would be a spike like tapping my finger on the mic.

*************
REQUIREMENTS

sudo apt install alsa-utils
You may have to change the --device setting for your system.

******
OPTIONAL INSTALLS

sudo apt install rng-tools

[/opt/kwynn/]kwutils.php is part of https://github.com/kwynncom/kwynn-php-general-utils

Its main use in this case is that it turns warnings, notices, etc. into exceptions that I don't catch in this case, so the program dies.  This setting will keep you 
from getting pages of annoying errors; in such an event, the first one will kill the program.  
**************
RNGTEST

sudo apt install rng-tools

rngtest (v5) - Copyright (c) 2004 by Henrique de Moraes Holschuh

With small input--a 1 second recording--sample output:

rngtest 5
Copyright (c) 2004 by Henrique de Moraes Holschuh
This is free software; see the source for copying conditions.  There is NO warranty; not even for MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.

rngtest: starting FIPS tests...
rngtest: entropy source drained
rngtest: bits received from input: 334000
rngtest: FIPS 140-2 successes: 16
rngtest: FIPS 140-2 failures: 0
rngtest: FIPS 140-2(2001-10-10) Monobit: 0
rngtest: FIPS 140-2(2001-10-10) Poker: 0
rngtest: FIPS 140-2(2001-10-10) Runs: 0
rngtest: FIPS 140-2(2001-10-10) Long run: 0
rngtest: FIPS 140-2(2001-10-10) Continuous run: 0
rngtest: input channel speed: (min=6.209; avg=15.685; max=18.626)Gibits/s
rngtest: FIPS tests speed: (min=51.971; avg=61.243; max=62.949)Mibits/s
rngtest: Program run time: 5048 microseconds


RNGD NOTE

Note that if you feed rngd an Ubuntu ISO or some such, it will silently reject the file as non-random, and /dev/random will not advance based on the ISO 
file.  (It will advance a bit based on your keystrokes and mouse movement.)


RELATED READING

*******************
This defines the FIPS 140-2 algorithms and their limitations

https://locard.eu/attachments/article/71/fips.pdf
"On the unbearable lightness of FIPS 140-2 randomness tests" by Darren Hurley-Smith, Constantinos Patsakis (Member, IEEE), and Julio Hernandez-Castro

This article has been accepted for publication in a future issue of this journal, but has not been fully edited. Content may change prior to final 
publication. Citation information: DOI 10.1109/TIFS.2020.2988505, IEEE Transactions on Information Forensics and Security

*********************

In the following blog entry, with audio file, I show a non-music file played as "music" that miserably fails randomness.  Note that in order to play the raw output 
of my program in VLC or other media players, you have to go through the process I describe in that entry to add a WAV header.

https://kwynn.com/t/7/11/blog.html#2020-1120-arb-file-music

https://wiki.archlinux.org/index.php/Rng-tools


FUTURE WORK

Write an output filter for rngtest.  rngtest is way too "noisy" for my usual purpose.

Auto-detect the microhpone.

The rngd man page says that the input "must support the Linux kernel /dev/random ioctl API."  I may look into that.  

On a related point is turning this into a lower level, hwrand system.

Yet another related point is writing this in C / C++.  

If I could get the very basic / raw voltage readouts, and there are enough bits of data, the low order bits should be just as random as a sound recording circuit.


FUTURE WORK - LIGHTNING DETECTOR

I'm reasonably sure the same principle can be used to create a lightning detector.  In the case of the lightning detector, one would use more bytes or 
all bytes.  I believe it would also work without any wire / headphones at all.

Regarding lightning detection:

https://thehackerdiary.wordpress.com/2017/05/24/lightning-detector-with-nothing-but-a-headphone-jack/
-- Lightning detector with a simple headphone jack  MAY 24, 2017 by 153ARMSTRONG


Note that I occasionally hear radio wave "whistlers" when I play the audio file.  These are caused by lightning and, I think, other causes.

**************************
SIGNAL RATHER THAN NOISE - UPDATE 2021/01/27

See https://github.com/kwynncom/code-fragments/blob/14feac9521d92cef942a60513493530c41b66e28/shortwave/README.md

In that experiment, I am seeking a signal from all the bytes rather than seeking random noise from one byte (out of 4 bytes).  I had assumed when 
I wrote this random app that I was using the lowest-endian byte, and I probably am: I have not re-checked.  I misunderstood what the numbers looked 
like, though.  In the signal experiment's README, I start to discuss the actual range of values.

Also, based on my first experiments, I may be able to get something like 23 bits of randomness per sample (of 32 bits) rather than 8. 
