from flask import Flask
from flask import jsonify
from flask_restful import Api, Resource, reqparse, abort, fields, marshal_with
from flask_sqlalchemy import SQLAlchemy
from flaskext.mysql import MySQL
import MySQLdb as sql
from levenshtein import match
import base64

app = Flask(__name__)
api = Api(app)
mysql = MySQL()
#app.config['SQLALCHEMY_DATABASE_URI'] = "mysql:///databaseT.db"
app.config['SQLALCHEMY_DATABASE_URI'] = "mysql://zib:Z00m1nt0B00ks@localhost/zib"
#^name of database?
db = SQLAlchemy(app)

#def see_bin_data():
	#with open("Images-imglist.txt", 'rb') as file:
		#binaryData = file.read()
	#return binaryData

#bin_data = see_bin_data()

class PublisherModel(db.Model):
	__tablename__ = "publisher"

	publisher = db.Column(db.Integer, primary_key=True)
	name = db.Column(db.String)

class BookModel(db.Model):	 
	__tablename__ = "book"
    
	isbn = db.Column(db.Integer, primary_key=True)
	title = db.Column(db.String)
	author = db.Column(db.String)
	edition = db.Column(db.String)
	publisher = db.Column(db.Integer, db.ForeignKey("publisher.publisher"))
	book_type = db.Column(db.String)
	ar_blob = db.Column(db.LargeBinary)
	ocr_blob = db.Column(db.String)
	resources = db.relationship("hasResourceModel", backref="resources", lazy=True)
	editors = db.relationship("editableByModel", backref="editors", lazy=True)
    
class ResourceModel(db.Model):
	__tablename__ =  "resource"

	rid = db.Column(db.Integer, primary_key=True)
	name = db.Column(db.String)
	url = db.Column(db.String)
	display = db.Column(db.String)
	downloadable = db.Column(db.Boolean)
	resource_type = db.Column(db.Enum)
	ars = db.relationship("hasResourceModel", backref="ars", lazy=True)
    
class ARResourceModel(db.Model):
	__tablename__ = "ar_resource_link"

	isbn = db.Column(db.Integer, db.ForeignKey("book.isbn"), primary_key=True)
	ar_id = db.Column(db.Integer, primary_key=True)
	rid = db.Column(db.Integer, db.ForeignKey("resource.rid")) 

class OCRResourceModel(db.Model):
	__tablename__ = "ocr_resource_link"

	isbn = db.Column(db.Integer, db.ForeignKey("book.isbn"), primary_key=True)
	page = db.Column(db.Integer, primary_key=True)
	rid = db.Column(db.Integer, db.ForeignKey("resource.rid"))

class ResourceInstanceModel(db.Model):
	__tablename__ = "resource_instance"

	isbn = db.Column(db.Integer, db.ForeignKey("book.isbn"), primary_key=True)
	rid = db.Column(db.Integer, db.ForeignKey("resource.rid"), primary_key=True)



class BlobFormat(fields.Raw):
	def format(self, value):
		encoded_str = base64.b64encode(value).decode('ascii')
		return encoded_str

db.create_all()

#instance_get_args = regparse.RequestParser()
#instance_get_args.add_argument("isbn", type=int, help="ISBN required to access resource instance.", required=True)

book_fields = {
    'isbn' : fields.Integer,
    'title' : fields.String,
    'author' : fields.String,
    'ar_blob' : BlobFormat,
    'ocr_blob' : fields.String,
    'edition' : fields.String,
    'author' : fields.String
}

resource_fields = {
	'rid' : fields.Integer,
	'url' : fields.String,
	'downloadable' : fields.Boolean,
	'type' : fields.String
}

publisher_fields = {
	'publisher' : fields.Integer,
	'email' : fields.String
	#add email here
}

resource_list_fields = {
	'resources' : fields.List(fields.Nested(resource_fields), attribute="items")
}

search_list_fields = {
	''
}

everything_fields = {
	'basic_info' : fields.Nested(book_fields),
	'ar_resources' : fields.List(fields.Nested(resource_fields), attribute="ar_items"),
	'publisher_info' : fields.Nested(publisher_fields)
}

#r = ResourceModel(rid=1, url="www.google.com", img_type="overlay", downloadable=True)
#db.session.add(r)
#r = ResourceModel(rid=2, url="www.microsoft.com", img_type="url", downloadable=False)
#db.session.add(r)
#hr = hasResourceModel(isbn=1, ar_id=1, rid=1)
#db.session.add(hr)
#db.session.commit()
#hr = hasResourceModel(isbn=1, ar_id=2, rid=2)
#db.session.add(hr)
#db.session.commit()



def abort_if_invalid(isbn):
	if False:
		abort(404, message="Invalid ISBN")

class Book(Resource):
	@marshal_with(everything_fields)
	def get(self, isbn):
		basic_result = BookModel.query.get(int(isbn))
		publisher_info = db.session.query(PublisherModel).join(BookModel, BookModel.pub_id == PublisherModel.pub_id).filter(BookModel.isbn == isbn).first()
		ar_result = db.session.query(ResourceModel).join(ARResourceModel, ARResourceModel.rid == ResourceModel.rid).filter(ARResourceModel.isbn == isbn).order_by(ARResourceModel.ar_id).all()
		ocr_result = db.session.query(ResourceModel).join(OCRResourceModle, OCRResourceModel.rid == ResourceModel.rid).filter(OCRResourceModel.isbn == isbn).order_by(OCRResourceModel.page).all()
		return { 'basic_info' : basic_result, 'ar_items' : ar_result, 'publisher_info' : publisher_info}, 201

class Publisher(Resource):
	def get(self, title):
		titles = db.session.query(BookModel.title, BookModel.isbn).all()
		titles_list = [[t[0].lower(), t[1], 0] for t in titles]
		results = match(title, titles_list)
		#TODO: use search_result_fields
		return results, 201


api.add_resource(Book, "/console/books/blobs/<int:isbn>")
api.add_resource(Publisher, "/console/books/blobs/<title>")

if __name__ == '__main__':
	app.run(debug=True)
