<x-markdown>
For the purpose of [controller bookings]({{ route('site.atc.bookings') }}),  
the **Primary Sectors** are defined as:

- SCO_CTR  
- LON_N_CTR  
- LON_C_CTR  
- LON_S_CTR  
- LON_W_CTR  

The **Secondary Sectors** are defined as:

- LON_NW_CTR  
- LON_NE_CTR  
- LON_M_CTR  
- LON_E_CTR  
- LON_D_CTR  
- LTC_N_CTR  
- LTC_S_CTR  
- MAN_W_CTR  
- MAN_NE_CTR  
- MAN_SE_CTR  
- SCO_WD_CTR  
- SCO_D_CTR  
- SCO_S_CTR  
- STC_CTR  
- STC_A_CTR  

UK home-rated or visiting controllers may open either a single Primary or Secondary sector,  
or a valid combination of Primary (e.g. `LON_SC_CTR`) or Secondary (e.g. `LTC_CTR`, `MAN_CTR`) sectors.

Further splits require the remaining portion of the Primary or Secondary sector to be staffed too —  
e.g. opening `LTC_NE_CTR` requires `LTC_NW_CTR` (as the remaining portion of `LTC_N_CTR`) to be online.  

Splits not defined in the London or Scottish FIR (EGTT) **vMATS Part 2** require specific approval from the Operations Department in the form of a  
[Temporary Instruction](https://community.vatsim.uk/forum/240-atc-temporary-instructions/)  
or permanent [Procedure Change](https://community.vatsim.uk/forum/166-atc-procedure-changes/).
</x-markdown>