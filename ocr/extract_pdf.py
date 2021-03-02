from pdfminer.converter import TextConverter
from pdfminer.layout import LAParams
from pdfminer.pdfdocument import PDFDocument
from pdfminer.pdfinterp import PDFResourceManager, PDFPageInterpreter
from pdfminer.pdfpage import PDFPage
from pdfminer.pdfparser import PDFParser

from io import StringIO
import sys
import mlcrate as mlc
import string
import json

parse_pages = [int(i) for i in sys.argv[2].split(',')]

pages = {}

with open(sys.argv[1], 'rb') as in_file:
    parser = PDFParser(in_file)
    doc = PDFDocument(parser)
    rsrcmgr = PDFResourceManager()

    for i, page in enumerate(PDFPage.create_pages(doc)):
      if i not in parse_pages:
        continue
      if i > max(parse_pages):
        break
      #print(i)

      output_string = StringIO()
      device = TextConverter(rsrcmgr, output_string, laparams=LAParams())
      interpreter = PDFPageInterpreter(rsrcmgr, device)
      interpreter.process_page(page)

      output = ''.join(s for s in output_string.getvalue() if s in string.printable)
      pages[i] = output

      #print(output)

# mlc.save(pages, sys.argv[3])
if sys.argv[3] == '-':
    print(json.dumps(pages))
else:
    json.dump(pages, open(sys.argv[3], 'w'))

# print(output_string.getvalue())
