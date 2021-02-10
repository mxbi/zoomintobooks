from pdfminer.converter import TextConverter
from pdfminer.layout import LAParams
from pdfminer.pdfdocument import PDFDocument
from pdfminer.pdfinterp import PDFResourceManager, PDFPageInterpreter
from pdfminer.pdfpage import PDFPage
from pdfminer.pdfparser import PDFParser

from io import StringIO
import sys
import mlcrate as mlc

parse_pages = sys.argv[2].split(',')

pages = {}

with open(sys.argv[1], 'rb') as in_file:
    parser = PDFParser(in_file)
    doc = PDFDocument(parser)
    rsrcmgr = PDFResourceManager()

    for i, page in enumerate(PDFPage.create_pages(doc)):
      if str(i) not in parse_pages:
        continue
      print(i)

      output_string = StringIO()
      device = TextConverter(rsrcmgr, output_string, laparams=LAParams())
      interpreter = PDFPageInterpreter(rsrcmgr, device)
      interpreter.process_page(page)

      output = output_string.getvalue()
      pages[i] = output

      print(output)

mlc.save(pages, sys.argv[3])

# print(output_string.getvalue())