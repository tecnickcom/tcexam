# OMR scan uploads

Place the folders containing the OMR (Optical Mark Recognition) scanned files here, so TCExam can
import answers from paper answer sheets.

Image file naming convention, where `[USRREG]` is the user's registration code:

| File | Contents |
|------|----------|
| `OMR_[USRREG]_QR.png` | the image containing the QR code |
| `OMR_[USRREG]_A[X].png` | the image containing the answers, where `[X]` is the sheet number |
